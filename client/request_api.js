export class RequestApi {
  constructor(body) {
    this.setting = {
      endpoint: "http://uranow.jp/endpoint/",
      client_id: "@your client id",
      secret: "@your secret-key"
    };

    this.body = JSON.stringify(body);
    this.timestamp = new Date().toISOString();
  }

  async createSignature() {
    const encoder = new TextEncoder();
    const key = await crypto.subtle.importKey(
      "raw",
      encoder.encode(this.setting.secret),
      { name: "HMAC", hash: "SHA-256" },
      false,
      ["sign"]
    );

    const signature = await crypto.subtle.sign(
      "HMAC",
      key,
      encoder.encode(this.timestamp + "\n" + this.body)
    );

    return Array.from(new Uint8Array(signature))
      .map(b => b.toString(16).padStart(2, "0"))
      .join("");
  }

  async send() {
    const signature = await this.createSignature();

    const headers = {
      "Content-Type": "application/json",
      "X-TIMESTAMP": this.timestamp,
      "X-SIGNATURE": signature
    };

    const response = await fetch(this.setting.endpoint, {
      method: "POST",
      headers,
      body: this.body
    });

    return await response.text();
  }
}