<?php
namespace Huosdk;
class BenefitRuleDiscount {
    /*
     * 折扣设置规则
     * 
        1. 设定的续充 > 设定的首充
        2. 设定的续充、首充，都要大于后台给此渠道游戏设置的折扣
        3. 渠道给下级代理设置的折扣 > 后台给一级代理设置的折扣
        4. 最小值(渠道折扣，后台设置的玩家首充) < 一级渠道玩家首充
        5. 二级渠道与一级渠道类似
        6. 订单利润>=0
     * 
     * 2016年10月14日 18:48:18
     * 严旭
    */
    public function adminSetGame() {
    }

    public function adminSetAgentGame() {
    }

    public function AgentSetMem() {
    }

    public function AgentSetSub() {
    }

    public function SubSetMem() {
    }
}

