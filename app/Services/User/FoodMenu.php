<?php
namespace App\Services\User;

class FoodMenu
{
    /**
     * 默认2个可选模版
     *
     * @var array
     */
    private $defaultTemplate = [
        'XFUN--1000' => [
            'breakfast' => '鸡蛋1、牛奶/酸奶/豆浆250ml、蒸包子1个
            上午喝水：{water_amount}ml',
            'lunch' => '米饭小半碗、白菜+菠菜150g、牛肉{meat_amount}g左右，做饭清炒即可
            下午：苹果200-300g，女生拳头大小
            下午喝水：{water_amount}ml',
            'dinner' => '奶昔一瓶，晚餐后如果饿可以加餐，可用牛奶250ml左右晚上喝水：{water_amount}ml',
        ],
        'XFUN--1500' => [
            'breakfast' => '6-10点：鸡蛋1、牛奶/酸奶/豆浆250ml、蒸包子1个-2个【适量即可】
            上午喝水：{water_amount}ml',
            'lunch' => '11点-13点：米饭小半碗--1碗【适量即可】、白菜+菠菜150g-200g、牛肉{meat_amount}g左右，做饭清炒即可
            下午：苹果200-300g，女生拳头大小
            下午喝水： {water_amount}ml',
            'dinner' => '7点之前：奶昔一瓶，生菜+花椰菜200g或者水果100-150g如果饿可以加餐，可用牛奶250ml左右
            晚上喝水：{water_amount}ml',
        ],
    ];


    /**
     * 客户饮食菜单数据
     *
     * @param array $userInfo       客户信息
     * @param array $lastAmount     客户上次的水量和肉量
     * @param integer $weightDelta  客户打卡的体重减少量
     * @return void
     */
    public function build(array $userInfo, array $lastAmount=[], float $weightDelta=0)
    {
        if ($this->isNotAdult($userInfo)) {
            return [
                'code' => 400,
                'messsage' => "未成年人不能推荐菜单"
            ];
        }

        if ($this->isPregnant($userInfo)) {
            return [
                'code' => 400,
                'messsage' => "孕妇孕妇或哺乳期不能推荐菜单"
            ];
        }

        if ($this->hasMajorDisease($userInfo)) {
            return [
                'code' => 400,
                'messsage' => "有重大疾病不能推荐菜单"
            ];
        }

        // 如果已有餐单记录，按打卡体重变化量更新旧菜单
        if ($this->existMenu($userInfo)) {
            
            $rate = $weightDelta / 5; 

            $meatAmountDelta = round($rate * 20, 2);
            $waterAmountDelta = round($rate * 150, 2);

            $amount = [
                'meat' => max(100, $lastAmount['meat'] - $meatAmountDelta),
                'water' => max(1500, $lastAmount['water'] - $waterAmountDelta)
            ];
        } else {

            // 计算BMI，判断体型
            $shapeJudge = $this->clcBmiValue($userInfo);
    
            // 根据体型代入身高和体重计算肉和水
            $amount = $this->clcWaterAndMeat($userInfo['weight'], $shapeJudge['level']);
        }

        // 渲染模版内容
        return [
            'code' => 200,
            'message' => '推荐菜单',
            'result' => [
                'amout' => $amount,
                'content' => $this->render($amount, $this->getTemplate($userInfo))
            ]
        ];
    }

    /**
     * 选择菜单模版
     *
     * @param array $userInfo
     * @return array
     */
    public function getTemplate(array $userInfo)
    {
        $reduceGoal = 0;
        if (isset($userInfo['expect_weight']) && isset($userInfo['weight'])) {
            $reduceGoal = floatval($userInfo['expect_weight'] - $userInfo['weight']);
        }

        if ($reduceGoal >= 5) {
            // 期望体重-初始体重>=5KG, 选用《XFUN--1000大卡饮食计划，月减5-6斤》模板
            return $this->defaultTemplate['XFUN--1000'];
        } else {
            // 期望体重-初始体重<5KG, 选用《XFUN--1500大卡饮食计划，月减3-4斤》模板
            return $this->defaultTemplate['XFUN--1500'];
        }
    }

    /**
     * 渲染模版
     *
     * @param array $amount
     * @param array $template
     * @return array
     */
    public function render(array $amount, array $template)
    {
        foreach($template as &$content) {
            $content = str_replace(['{water_amount}', '{meat_amount}'], [$amount['water'], $amount['meat']], $content);
        }
        return $template;
    }

    /**
     * 计算BMI指标值
     *
     * @param $userInfo
     * @return array
     */
    protected function clcBmiValue(array $userInfo)
    {
        $bmiVal = 0;
        if (isset($userInfo['height']) && isset($userInfo['weight']) && $userInfo['height'] > 0 && $userInfo['weight'] > 0) {
            $bmiVal = $userInfo['weight'] / ($userInfo['height'] * $userInfo['height']) > 0;
        }

        // 体形判断标准
        // 体质指数(BMI)=体重(kg)/身高(m)^2
        //      BMI<18.5   过低
        // 18.5≤BMI≤23.9   正常
        // 24.0≤BMI≤27.9   超重
        //      BMI≥28     肥胖

        switch($bmiVal) {
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

        switch($shapeLevel) {
            case 1:
            case 2:
                $meatMinAmount = 100;
                $waterMinAmount = 1500;
                $waterMaxAmount = 2000;
                $meatAmount = ($weight - 40) / 0.25;
                $waterAmount = $weight * 30;
                break;
            case 3:
                $meatMinAmount = 180;
                $waterMinAmount = 2000;
                $waterMaxAmount = 2800;
                $meatAmount = ($weight - 40) / 0.25;
                $waterAmount = $weight * 35;
                break;
            case 4:
                $meatMinAmount = 180;
                $waterMinAmount = 2000;
                $waterMaxAmount = 3000;
                $meatAmount = ($weight * 0.9 - 40) / 0.25;
                $waterAmount = $weight * 40;
                break;
            default:
                return [
                    'meat' => 100,
                    'water' => 1500
                ];
                break;
        }

        return [
            'meat' => max([$meatMinAmount, $meatAmount]),
            'water' => max([$waterMinAmount, min([$waterMaxAmount, $waterAmount])]),
        ];
    }

    /**
     * 是否成年
     *
     * @param $userInfo
     * @return boolean
     */
    protected function isNotAdult(array $userInfo)
    {
        return isset($userInfo['age']) && $userInfo['age'] < 18;
    }

    /**
     * 是否孕妇或哺乳期
     *
     * @param $userInfo
     * @return boolean
     */
    protected function isPregnant(array $userInfo)
    {
        return isset($userInfo['is_pregnant']) && $userInfo['is_pregnant'] > 0;
    }

    /**
     * 已经有餐单记录
     *
     * @param $userInfo
     * @return boolean
     */
    protected function existMenu(array $userInfo)
    {
        return isset($userInfo['is_exist_menu']) && $userInfo['is_exist_menu'] > 0;
    }
    
    /**
     * 是否有重大疾病
     *
     * @param $userInfo
     * @return boolean
     */
    protected function hasMajorDisease(array $userInfo)
    {
        return isset($userInfo['has_major_disease']) && $userInfo['has_major_disease'] > 0;
    }

    /**
     * 是否有一般疾病
     *
     * @param $userInfo
     * @return boolean
     */
    protected function hasGeneralDiseases(array $userInfo)
    {
        return isset($userInfo['has_general_diseases']) && $userInfo['has_general_diseases'] > 0;
    }

}