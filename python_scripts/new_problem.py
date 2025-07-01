import pymysql
import json

# 如果你映射了端口，比如把容器的3306映射到了宿主机3307，就用 localhost:3307
conn = pymysql.connect(
    host='localhost',   # 如果数据库运行在宿主机本地或通过端口映射
    port=3306,
    user='root',
    password='root',
    database='app_uoj233',
    charset='utf8mb4'
)

with conn.cursor() as cur:
    # 插入 problems 表
    cur.execute("INSERT INTO problems (title, is_hidden, submission_requirement) VALUES (%s, %s, %s)",
                ('New Problem', 1, '{}'))
    problem_id = conn.insert_id()

    # 插入 problems_contents 表
    cur.execute("INSERT INTO problems_contents (id, statement, statement_md) VALUES (%s, %s, %s)",
                (problem_id, '', ''))

    print(f"成功添加题目 ID: {problem_id}")


requirement = [{'name': 'answer', 'type': 'source code', 'file_name': 'answer.code'}]
    
with conn.cursor() as cur:
    cur.execute("UPDATE problems SET submission_requirement = %s WHERE id = %s",
                (json.dumps(requirement), problem_id))

conn.commit()
conn.close()