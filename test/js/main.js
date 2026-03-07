import { GetHtml } from "./get_html.js"

class Main{
  constructor(){
    this.init()
  }

  async init(){
    const res = await new GetHtml({
      url : "https://myntinc.com/"
    }).send()
    console.log(res)
  }
}

switch(document.readyState){
  case "complete":
  case "interactive":
    new Main();break
  default:
    window.addEventListener("DOMContentLoaded", (()=>new Main()))
}