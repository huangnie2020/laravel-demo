<?php

namespace App\Services\Mr;

class MenuService
{

    /**
     * 减重需求高
     *
     * @var integer
     */
    const PLAN_REDUCE_WEIGHT_MORE = 1;

    /**
     * 减重需求低
     *
     * @var integer
     */
    const PLAN_REDUCE_WEIGHT_LESS = 2;


    public function desc(){
        return [
            'production_standard'=>'代谢根据：正常代谢的轻体力女生基础代谢1200大卡，加上脑力消耗、体力活动，累计一天消耗1800大卡,蛋白质需求：正常女性需求65g/天, 水量：1500-1700ml，减肥者需要更多',
            'design_principle'=>'整份餐单在1000大卡左右,食物热效应扣除10%左右，实际吸收900大卡,每天有900大卡的能量亏损，根据能量守恒，1g脂肪是9大卡，900大卡/9=100g脂肪,理论上，这个餐单计划一周减0.7kg，一个月3kg，月减6斤。',
            'sensitive_food_handling'=>'如果餐单存在过敏的食物，可以从食物选择表进行更换同类别即可
                                    如牛奶过敏，可以用无糖豆浆/替代
                                    如喝牛奶拉肚子，可用零乳糖牛奶/豆浆/酸奶替代
                                    如果鸡蛋过敏，可用20g肉干或50g鲜肉替代',

        ];
    }

    public function food(){
        return [
            'breakfast' => [
                [
                    'key'=>'主食备选',
                    'value' =>'粥类、馒头、包子、面条、面包、玉米、红薯、紫薯、杂粮粥'
                ]
            ],
            'lunch' => [
                [
                    'key'=>'主食备选',
                    'value'=>'米饭、粥类、馒头、包子、面条、土豆、玉米、南瓜、红薯、紫薯、莲藕、山药、杂粮粥'
                ],
                [
                    'key'=>'蔬菜备选',
                    'value'=>'菠菜、白菜、生菜、韭菜、油菜（青菜）、茼蒿、西兰花、花椰菜、油麦菜、莴笋、芥蓝、上海青、菜心、青椒、菜椒'
                ],
                [
                    'key'=>'肉备选',
                    'value'=>'猪肉、牛肉、羊肉、鱼、虾、鸡肉、鸭肉、鹅肉'
                ],
                [
                    'key'=>'水果备选',
                    'value'=>'苹果、梨子、圣女果、草莓、桃子、橘子、橙子'
                ]
            ],
            'dinner' => [
                [
                    'key'=>'加餐备选',
                    'value'=>'风干牛肉干20g、豆干20g，鸡蛋白1个'
                ]
            ]
        ];
    }

    /**
     * 默认2个可选模版
     *
     * @var array
     */
    private $defaultTemplate = [
        'XFUN--1000' => [
            'breakfast' => "鸡蛋1、牛奶/酸奶/豆浆250ml、蒸包子1个\n上午喝水：{water}ml",
            'lunch' => "米饭小半碗、白菜+菠菜150g、牛肉{meat}g左右，做饭清炒即可\n下午：苹果200-300g，女生拳头大小\n下午喝水：{water}ml",
            'dinner' => '奶昔一瓶，晚餐后如果饿可以加餐，可用牛奶250ml左右 晚上喝水：{water}ml',
        ],
        'XFUN--1500' => [
            'breakfast' => "6-10点：鸡蛋1、牛奶/酸奶/豆浆250ml、蒸包子1个-2个【适量即可】\n上午喝水：{water}ml",
            'lunch' => "11点-13点：米饭小半碗--1碗【适量即可】、白菜+菠菜150g-200g、牛肉{meat}g左右，做饭清炒即可\n下午：苹果200-300g，女生拳头大小\n下午喝水： {water}ml",
            'dinner' => "7点之前：奶昔一瓶，生菜+花椰菜200g或者水果100-150g 如果饿可以加餐，可用牛奶250ml左右\n晚上喝水：{water}ml",
        ],
    ];


    /**
     * 客户饮食菜单数据
     *
     * @param integer $weight 体重
     * @param integer $height 身高
     * @param integer $plan 塑型计划
     * @return array
     */
    public function create(int $weight, int $height, int $plan)
    {
        // 计算BMI，判断体型
        $shapeJudge = $this->clcBmiValue($weight, $height);

        print_r(['j' => $shapeJudge]);

        // 根据体型代入身高和体重计算肉和水
        $amount = $this->clcWaterAndMeat($weight, $shapeJudge['level']);

        // 渲染模版内容
        return $this->render($amount, $plan);
    }

    /**
     * 客户饮食菜单数据
     *
     * @param array $lastMenu 上次菜单
     * @param integer $weightDelta 客户打卡的体重减少量
     * @param integer $plan 塑型计划，默认是上次的
     * @return array
     */
    public function update(array $lastMenu = [], int $weightDelta = 0, int $plan = 0)
    {
        $lastAmount = [
            'meat' => 0,
            'water' => 0
        ];

        foreach ($lastMenu as $item) {
            if (isset($item['meat'])) {
                // 肉量不能累加，只有水需要累加，这是由前面的处理流程决定
                $lastAmount['meat'] = $item['meat'];
                $lastAmount['water'] += $item['water'];
            }
        }

        // 如果已有餐单记录，按打卡体重变化量更新旧菜单
        $rate = floor($weightDelta / 5);

        $meatAmountDelta = intval($rate * 20);
        $waterAmountDelta = intval($rate * 150);

        $meatAmount = max(100, $lastAmount['meat'] - max($meatAmountDelta, 0));
        $waterAmount = max(1500, $lastAmount['water'] - max($waterAmountDelta, 0));

        // 水量按比例（上午：下午：晚上=3: 4：2）分配
        $amount = $this->getRateAmount($meatAmount, $waterAmount);

        // 渲染模版内容
        return $this->render($amount, $plan);
    }

    /**
     * 渲染模版
     *
     * @param array $amount
     * @param integer $plan
     * @return array
     */
    public function render(array $amount, int $plan)
    {
        $result = [];
        $template = $this->selectTemplate($plan);
        foreach ($template as $key => $content) {

            if (isset($amount[$key])) {
                $result[$key] = [
                    'meat' => $amount[$key]['meat'],
                    'water' => $amount[$key]['water'],
                    'text' => str_replace(['{water}', '{meat}'], [$amount[$key]['water'], $amount[$key]['meat']], $content)
                ];
            }
        }
        return $result;
    }

    /**
     * 选择菜单模版
     *
     * @param integer $plan 塑型计划
     * @return array
     */
    protected function selectTemplate(int $plan)
    {
        if ($plan == self::PLAN_REDUCE_WEIGHT_MORE) {
            // 期望体重-初始体重>=5KG, 选用《XFUN--1000大卡饮食计划，月减5-6斤》模板
            return $this->defaultTemplate['XFUN--1000'];
        } else {
            // 期望体重-初始体重<5KG, 选用《XFUN--1500大卡饮食计划，月减3-4斤》模板
            return $this->defaultTemplate['XFUN--1500'];
        }
    }

    /**
     * 计算BMI指标值
     *
     * @param integer $weight
     * @param integer $height cm
     * @return array
     */
    public function clcBmiValue(int $weight, int $height)
    {

        $bmiVal=$this->bmi($weight,$height);

        var_dump($bmiVal);
        // 体形判断标准
        //      BMI<18.5   过低
        // 18.5≤BMI≤23.9   正常
        // 24.0≤BMI≤27.9   超重
        //      BMI≥28     肥胖

        switch ($bmiVal) {
            case $bmiVal < 18.5:
                $shapeJudge = [
                    'level' => 1,
                    'description' => '过低'
                ];
                break;
            case $bmiVal <= 23.9:
                $shapeJudge = [
                    'level' => 2,
                    'description' => '正常'
                ];
                break;
            case $bmiVal <= 27.9:
                $shapeJudge = [
                    'level' => 3,
                    'description' => '超重'
                ];
                break;
            default:
                $shapeJudge = [
                    'level' => 4,
                    'description' => '肥胖'
                ];
                break;
        }

        return $shapeJudge;
    }

    public function bmi(int $weight, int $height){
        $height=bcdiv($height,100,2);
        $bmiVal = 0;
        if ($height > 0) {
            // 体质指数(BMI)=体重(kg)/身高(m)^2
            $bmiVal = bcdiv($weight,$height * $height, 2);
        }
        return $bmiVal;
    }

    /**
     * 计算BMI指标值
     *
     * @param integer $weight
     * @param integer $shapeLevel
     * @return array
     */
    protected function clcWaterAndMeat($weight, int $shapeLevel)
    {
        // BMI过低
        // 肉量，按{体重【KG】*1-40}/0.25=？     计算肉量，100g设定为最低值
        // 水量，按{体重【KG】*30=？             计算水量，设定1500为下限，2200为上限

        // BMI正常
        // 肉量，按{体重【KG】*1-40}/0.25=？     计算肉量，100g设定为最低值
        // 水量，按{体重【KG】*30=？             计算水量，设定1500为下限，2200为上限

        // BMI超重
        // 肉量，按{体重【KG】*1-40}/0.25=？     计算肉量， 180g设定为最高值
        // 水量，按{体重【KG】*35=？             计算水量，设定2000为下限，2800为上限

        // BMI肥胖
        // 肉量，按{体重【KG】*0.9-40}/0.25=？   计算肉量，180g设定为最高值
        // 水量，按{体重【KG】*40=？             计算水量，设定2000为下限，3000为上限

        // 经过体重的计算之后，得出一整天需要的肉和水，水按照以下比例分配
        // 按照喝水的比例：上午：下午：晚上=3: 4：2
        // 把总的饮水量进行分配

        $meatAmount = 100;
        $waterAmount = 1500;
        $waterMinAmount = 1500;
        $waterMaxAmount = 2000;
    
        switch ($shapeLevel) {
            case 1:
            case 2:
                $meatAmount = ($weight - 40) / 0.25;
                $waterAmount = $weight * 30;
                break;
            case 3:
                $waterMinAmount = 2000;
                $waterMaxAmount = 2800;
                $meatAmount = ($weight - 40) / 0.25;
                $waterAmount = $weight * 35;
                break;
            case 4:
                $waterMinAmount = 2000;
                $waterMaxAmount = 3000;
                $meatAmount = ($weight * 0.9 - 40) / 0.25;
                $waterAmount = $weight * 40;
                break;
        }

        if ($shapeLevel == 1 || $shapeLevel == 2) {
            $meatMinAmount = 100;
            $meatAmount = max([$meatMinAmount, $meatAmount]);
        } else {
            $meatMaxAmount = 180;
            $meatAmount = min([$meatMaxAmount, $meatAmount]);
        }

        $waterAmount = max([$waterMinAmount, min([$waterMaxAmount, $waterAmount])]);

        // 水量按比例（上午：下午：晚上=3: 4：2）分配
        return $this->getRateAmount($meatAmount, $waterAmount);
    }

    /**
     * 按比例分配水量
     * 水量按比例（上午：下午：晚上=3: 4：2）分配
     *
     * @param integer $meatAmount
     * @param integer $waterAmount
     * @return array
     */
    protected function getRateAmount(int $meatAmount, int $waterAmount)
    {
        $meatAmount = intval($meatAmount);

        return [
            'breakfast' => [
                'meat' => $meatAmount,
                'water' => intval($waterAmount * 3 / 9)
            ],
            'lunch' => [
                'meat' => $meatAmount,
                'water' => intval($waterAmount * 4 / 9)
            ],
            'dinner' => [
                'meat' => $meatAmount,
                'water' => intval($waterAmount * 2 / 9)
            ],
        ];
    }

}
