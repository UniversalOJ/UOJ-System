import json
import pymysql
import zipfile
import os, re
import random
import string
import sys
from pathlib import Path
import argparse

from database_interface import UOJDatabaseInterface, sync_problem
from compile_checker import compile_checker
from data_preprocess import renamePrefixToA, generate_problem_conf

def main():
    parser = argparse.ArgumentParser(description="批量配置UOJ题目")
    parser.add_argument("submission_id", type=str, help="题目ID")
    parser.add_argument("--db_host", type=str, default="uoj-db", help="数据库主机")
    parser.add_argument("--db_user", type=str, default="root", help="数据库用户名")
    parser.add_argument("--db_password", type=str, default="root", help="数据库密码")
    parser.add_argument("--db_name", type=str, default="app_uoj233", help="数据库名称")
    parser.add_argument("--uoj_problem_base", type=str, default="/var/uoj_data/", help="UOJ题目存储路径")
    parser.add_argument("--storage_path", type=str, default="/opt/uoj/web/app/storage/", help="存储路径")

    args = parser.parse_args()

    db_config = {
        'host': args.db_host,
        'port': 3306,
        'user': args.db_user,
        'password': args.db_password,
        'database': 'app_uoj233',
    }

    db_interface = UOJDatabaseInterface(db_config, args.storage_path)
    
    db_interface.reconnect()
    submission_result = db_interface.fetch_result(args.submission_id)
    details = submission_result.get("details", "")
    matches = re.findall(r'info="([^"]+)"', details)
    info = matches[-1] if matches else "N/A"
    res_s = {"score": submission_result.get("score", 0), "info": info, "submission_id": args.submission_id}
    if "error" in submission_result:
        res_s["info"] = submission_result["error"]
    print(json.dumps(res_s, ensure_ascii=False, indent=4))

if __name__ == "__main__":
    main()