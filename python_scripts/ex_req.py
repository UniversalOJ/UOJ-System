import pymysql
import json

conn = pymysql.connect(
    host='localhost',   # 如果数据库运行在宿主机本地或通过端口映射
    port=3306,
    user='root',
    password='root',
    database='app_uoj233',
    charset='utf8mb4'
)

problem_id = 7

requirement = [{'name': 'answer', 'type': 'source code', 'file_name': 'answer.code'}]
    
with conn.cursor() as cur:
    cur.execute("UPDATE problems SET submission_requirement = %s WHERE id = %s",
                (json.dumps(requirement), problem_id))