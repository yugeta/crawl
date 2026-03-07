import json
import os
import hmac
import hashlib
import requests
from datetime import datetime, timezone

class RequestApi:
    def __init__(self, body, setting_json_path=None):
        if setting_json_path is None:
            setting_json_path = os.path.join(os.path.dirname(__file__), "setting.json")
        self.setting_json_path = setting_json_path
        self.body = json.dumps(body, ensure_ascii=False)
        self.setting = self.load_setting()
        self.timestamp = datetime.now(timezone.utc).isoformat()
        self.signature = self.create_signature()
        self.headers = self.create_header()
        self.datas = self.endpoint_send()

    def load_setting(self):
        if not os.path.isfile(self.setting_json_path):
            raise FileNotFoundError('Not found "setting.json"')
        with open(self.setting_json_path, encoding="utf-8") as f:
            return json.load(f)

    def create_signature(self):
        msg = f"{self.timestamp}\n{self.body}".encode("utf-8")
        secret = self.setting["secret"].encode("utf-8")
        return hmac.new(secret, msg, hashlib.sha256).hexdigest()

    def create_header(self):
        return {
            "Content-Type": "application/json",
            "X-TIMESTAMP": self.timestamp,
            "X-SIGNATURE": self.signature,
        }

    def endpoint_send(self):
        response = requests.post(
            self.setting["endpoint"],
            data=self.body.encode("utf-8"),
            headers=self.headers,
        )
        return response.text