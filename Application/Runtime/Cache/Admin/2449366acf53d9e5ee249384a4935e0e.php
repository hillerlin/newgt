<?php if (!defined('THINK_PATH')) exit();?><style>
.pbox{width: 1100px;margin: 0 auto;}
.titleDiv{position: relative;}
.titleDiv li{font-size: 14px;font-weight: bold;text-align: center;}
.titleDiv li:nth-child(1){width: 152px;margin: 15px 0 0;}
.titleDiv li:nth-child(2){position: absolute;top: 164px;right:86px;}
.titleDiv li:nth-child(3){position: absolute;top: 164px;left:48px;}
.titleDiv li:nth-child(4){position: absolute;top: 286px;right:86px;}
.process ul{text-align: center;padding: 24px 0 0;clear:both;overflow: hidden;}
.process ul li{float: left;width: 152px;margin: 0 30px 50px 0;position: relative;}
.process ul:nth-child(2){padding-right: 188px;}
.process ul:nth-child(n+3){padding-top: 38px;}
.process ul:nth-child(5){padding-top: 52px;}
.process ul:nth-child(4){padding-right: 6px;}
.process ul:nth-child(2n) li{float: right;}
.process ul li.large{margin: -10px 30px 40px 0;}
.process ul li.large span.go{top: 10px;}
.process ul li .deal{color:#fff;line-height: 22px;font-size: 13px;padding: 6px 0;border: 1px #ddd solid;background: #999;}
.process ul li .deal.start,.process ul li .deal.end{border-radius: 30px;}
.process ul li .deal.judge{border: none;background: none;}
.process .judge b{color:#fff;position:relative;z-index:2;font-weight: normal;}
.process .judge span{display: inline-block;width: 152px;height: 152px;background: #999;border: 1px #ddd solid;position: absolute;left: 0;top: -56px;z-index:1;transform:scale(0.7,0.4) rotate(45deg);
    -webkit-transform:scale(0.7,0.4) rotate(45deg); 
    -moz-transform:scale(0.7,0.4) rotate(45deg); 
    -o-transform:scale(0.7,0.4) rotate(45deg);}
.process ul li.pass .deal{color: #fff;background: #009c00;border-color:#f0f0f0;}
.process ul li.pass .judge span{background: #009c00;border-color:#f0f0f0;}
.process ul li.check .deal{color: #fff;border-color:#f0f0f0;}
.process ul li.check .judge span{background: #d62c2c;border-color:#f0f0f0;}
.process ul li span.go{font-size: 32px;line-height: 31px;position: absolute;top: 0;right: -31px;}
.process ul li span.start,.process ul li span.go.goon{top: -34px;right: 68px;}
.process ul li span.go.goon2{top: -58px;}
.process ul li span.tab{top: 54px;right: 68px;}
.process ul li span.gonext{top:0;right: 150px;}
.process ul li span.go span{width: 32px;position: absolute;top:-6px;left: 0;}
</style>
<div class="bjui-pageContent">
    <div class="pbox">
        <div class="titleDiv">
            <ul>
                <li>项目立项</li>
                <li>风控审核</li>
                <li>合同签订</li>
                <li>放款审核</li>
            </ul>
        </div>
        <div class="process">
            <ul>
                <li class="pass"><div class="deal start">开 始</div><span class="go start">↓</span><span class="go">→</span></li>
                <li class="large pass"><div class="deal">项管经理/项管专员<br />创建项目</div><span class="go">→</span></li>
                <li class="pass"><div class="deal">项管总监分配任务</div><span class="go">→</span></li>
                <li class="check"><div class="deal judge"><b>项管专员审核</b><span></span></div><span class="go">→</span></li>
                <li><div class="deal">风控初审</div><span class="go">→</span></li>
                <li><div class="deal judge"><b>项管专员发布立项会</b><span></span></div><span class="go tab">↓</span></li>
            </ul>
            <ul>
                <li><div class="deal">风控总监分配尽调人员</div><span class="go">←</span></li>
                <li class="large"><div class="deal">尽调人员<br />创建项目</div><span class="go">←</span></li>
                <li><div class="deal judge"><b>项管专员发布风控会</b><span></span></div><span class="go">←</span></li>
                <li><div class="deal">尽调人员上传审核意见书</div><span class="go">←</span><span class="go gonext">←</span></li>
            </ul>
            <ul>
                <li class="large"><div class="deal">项管专员从通过风控的项目中选择项目发起签约</div><span class="go goon">↓</span><span class="go">→</span></li>
                <li><div class="deal judge"><b>风控总监审核</b><span></span></div><span class="go">→</span></li>
                <li><div class="deal judge"><b>副总裁审核</b><span></span></div><span class="go">→<span>通过</span></span></li>
                <li><div class="deal judge"><b>总裁审核</b><span></span></div><span class="go">→</span></li>
                <li><div class="deal">法务上传合同文本</div><span class="go">→</span></li>
            </ul>
            <ul>
                <li class="large"><div class="deal">项管专员从签约项目中选择项目</div><span class="go goon">↓</span></li>
                <li><div class="deal judge"><b>法务审核创建项目</b><span></span></div><span class="go">←</span></li>
                <li><div class="deal judge"><b>项目主审人审核</b><span></span></div><span class="go">←</span></li>
                <li><div class="deal judge"><b>贷中审核人审核</b><span></span></div><span class="go">←</span></li>
                <li><div class="deal judge"><b>风控总监审核</b><span></span></div><span class="go">←</span></li>
                <li><div class="deal judge"><b>副总裁审核</b><span></span></div><span class="go">←</span></li>
            </ul>
            <ul>
                <li><div class="deal judge"><b>总裁审核</b><span></span></div><span class="go goon goon2">↓</span><span class="go">→</span></li>
                <li><div class="deal">财务总监确认</div><span class="go">→</span></li>
                <li><div class="deal">出纳确认</div><span class="go">→</span></li>
                <li><div class="deal end">放款成功</div></li>
            </ul>
        </div>
    </div>
</div>
<div class="bjui-pageFooter">
    <ul>
        <li><button type="button" class="btn-close" data-icon="close">取消</button></li>
    </ul>
</div>