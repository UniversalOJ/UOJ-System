import json
import pymysql # 或其他数据库驱动
import zipfile
import os
import random
import string
from pathlib import Path

class UOJResultFetcher:
    def __init__(self, db_config):
        """
        初始化数据库连接
        """
        try:
            self.connection = pymysql.connect(**db_config, charset='utf8mb4')
            self.cursor = self.connection.cursor()
            print("✓ 数据库连接成功")
        except Exception as e:
            print(f"✗ 数据库连接失败: {e}")
            self.connection = None

    def fetch_result(self, submission_id):
        """
        获取提交结果
        """
        if not self.connection:
            print("✗ 无法获取结果: 数据库连接未建立")
            return None
        
        try:
            self.cursor.execute("SELECT result FROM submissions WHERE id = %s", (submission_id,))
            result = self.cursor.fetchone()
            if result:
                json_str = result[0].decode('utf-8')
                res = json.loads(json_str)
                
                return json.loads(json_str)
            else:
                print(f"✗ 提交 ID {submission_id} 不存在")
                return None
        except Exception as e:
            print(f"✗ 获取提交结果失败: {e}")
            return None

    def close(self):
        if self.connection:
            self.connection.close()
            print("✓ 数据库连接已关闭")

# --- 使用示例 ---
if __name__ == '__main__':
    # 1. 请替换为您的数据库配置
    db_config = {
        'host': 'localhost',
        'port': 3306,
        'user': 'root',
        'password': 'root',
        'database': 'app_uoj233',
    }

    submission_id = 1

    fetch_resulter = UOJResultFetcher(db_config)
    result = fetch_resulter.fetch_result(submission_id)
    print(result)

    # 6. 关闭连接
    fetch_resulter.close()