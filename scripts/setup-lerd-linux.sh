#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
export PATH="${HOME}/.local/bin:${PATH}"

log() { printf '==> %s\n' "$*"; }
warn() { printf '!! %s\n' "$*" >&2; }
need_sudo() {
  log "需要 sudo 密碼（一次性）：$*"
  sudo "$@"
}

ensure_lerd_cli() {
  if command -v lerd >/dev/null 2>&1; then
    log "Lerd CLI 已安裝：$(lerd --version)"
    return
  fi

  log "安裝 Lerd CLI 到 ~/.local/bin"
  tmp="$(mktemp -d)"
  trap 'rm -rf "$tmp"' RETURN
  curl -fsSL -o "${tmp}/lerd.tar.gz" \
    https://github.com/geodro/lerd/releases/download/v1.23.1/lerd_1.23.1_linux_amd64.tar.gz
  tar -xzf "${tmp}/lerd.tar.gz" -C "${tmp}"
  install -m 755 "${tmp}/lerd" "${HOME}/.local/bin/lerd"
  log "Lerd CLI 已安裝：$(lerd --version)"
}

ensure_prerequisites() {
  local missing=()
  command -v podman >/dev/null 2>&1 || missing+=(podman)
  command -v certutil >/dev/null 2>&1 || missing+=(libnss3-tools)

  if ((${#missing[@]} > 0)); then
    log "安裝系統套件：${missing[*]}"
    need_sudo apt-get update -qq
    need_sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq podman libnss3-tools
  else
    log "系統套件已就緒（podman、certutil）"
  fi

  local port_start
  port_start="$(sysctl -n net.ipv4.ip_unprivileged_port_start)"
  if [[ "${port_start}" -gt 80 ]]; then
    log "設定 rootless Nginx 可使用 80/443（目前 ip_unprivileged_port_start=${port_start}）"
    need_sudo sysctl -w net.ipv4.ip_unprivileged_port_start=80
    if [[ ! -f /etc/sysctl.d/99-lerd-unprivileged-ports.conf ]]; then
      echo 'net.ipv4.ip_unprivileged_port_start=80' | need_sudo tee /etc/sysctl.d/99-lerd-unprivileged-ports.conf >/dev/null
      need_sudo sysctl --system >/dev/null
    fi
  else
    log "ip_unprivileged_port_start 已符合 Lerd 需求（${port_start}）"
  fi
}

lerd_is_ready() {
  lerd status >/dev/null 2>&1
}

bootstrap_lerd() {
  if lerd_is_ready; then
    log "Lerd 已初始化，略過 lerd install"
  else
    log "初始化 Lerd（rootless Podman）"
    lerd install || warn "lerd install 回報警告（DNS sudoers 等），若 lerd status 正常可忽略"
  fi

  log "啟動 Lerd 服務"
  lerd start
}

verify_api() {
  local response
  response="$(curl -ks --max-time 30 "https://weather-map.test/api/weather.php?lat=25.04&lon=121.52")"

  if [[ -z "${response}" ]]; then
    warn "API 驗證失敗：未取得回應"
    return 1
  fi

  if command -v jq >/dev/null 2>&1; then
    jq -r '"\(.location.county) / \(.current.temperature)°C"' <<<"${response}"
  else
    printf '%s\n' "${response}" | head -c 200
    printf '\n'
  fi
}

link_project() {
  cd "${ROOT}"
  log "同步 API 到 public/api/"
  npm run sync-api
  log "連結專案站台"
  lerd link
  log "驗證 API"
  verify_api
  log "完成。日常開發：lerd start && npm run dev"
  log "前端熱更新：http://localhost:3000"
  log "靜態預覽（需 npm run build）：https://weather-map.test"
}

main() {
  ensure_lerd_cli
  ensure_prerequisites
  bootstrap_lerd
  link_project
}

main "$@"
