#!/usr/bin/env bash
set -euo pipefail

# === 可配置 ===
IMAGE="ghcr.io/universaloj/uoj-judger:latest"
NETWORK="uoj-system_default"
UOJ_HOST_NAME="uoj-web"            # 与 UOJ 主容器在同一网络下的容器名；用名字避免写死 IP
MYSQL_CONT="uoj-db"            # MySQL 容器名
MYSQL_DB="app_uoj233"
MYSQL_USER="root"
MYSQL_PASS="root"
JUDGER_PASS_DEFAULT="changeme" # 如果不想每台都不同，就用统一密码
CPU_LIMIT=""                   # 例如 "--cpus=1.0"
MEM_LIMIT=""                   # 例如 "--memory=1g"

# 用法： ./spawn_judgers.sh 数量 前缀(可选) 密码(可选)
COUNT="${1:-3}"
NAME_PREFIX="${2:-judger}"
JUDGER_PASS="${3:-$JUDGER_PASS_DEFAULT}"

mkdir -p confs

# 确保网络存在
docker network inspect "$NETWORK" >/dev/null 2>&1 || docker network create "$NETWORK"

for i in $(seq 1 "$COUNT"); do
  NAME="${NAME_PREFIX}${i}"
  CONF="confs/conf-${NAME}.json"

  # 生成每台评测机的 conf.json
  cat > "$CONF" <<EOF
{
  "uoj_protocol": "http",
  "uoj_host": "${UOJ_HOST_NAME}",
  "judger_name": "${NAME}",
  "judger_password": "${JUDGER_PASS}",
  "socket_port": 2333,
  "socket_password": "${JUDGER_PASS}"
}
EOF

  # 如已存在，先删容器（不删镜像）
  if docker ps -a --format '{{.Names}}' | grep -q "^${NAME}$"; then
    docker rm -f "${NAME}" >/dev/null
  fi

  # 拉起容器：共享同一镜像，外挂只读 conf，限制资源（按需启用）
  docker run -d --name "${NAME}" \
    --cap-add SYS_PTRACE \
    --restart=always \
    --network "${NETWORK}" \
    -v "$(pwd)/${CONF}:/opt/uoj_judger/.conf.json:ro" \
    $CPU_LIMIT $MEM_LIMIT \
    --entrypoint /bin/bash \
    "${IMAGE}" -lc '/opt/up; touch /opt/uoj_judger/log/judge.log; tail -F /opt/uoj_judger/log/judge.log /opt/uoj_judger/log/judge_client.log 2>/dev/null || sleep infinity'

  # 取容器 IP（在自定义网络下）
  IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "${NAME}")

  # 写入 MySQL（入库一次即可）
  docker exec -i "${MYSQL_CONT}" \
    mysql -u"${MYSQL_USER}" -p"${MYSQL_PASS}" "${MYSQL_DB}" \
    -e "INSERT INTO judger_info (judger_name, password, ip) VALUES ('${NAME}', '${JUDGER_PASS}', '${IP}')
        ON DUPLICATE KEY UPDATE password=VALUES(password), ip=VALUES(ip);"

  echo "Launched ${NAME} at ${IP}"
done

echo "Done."
