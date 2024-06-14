<?php

namespace widget;

use app\common\lib\Widget;
use app\common\logic\IndexLogic;

class ThisYearSaleDetail extends Widget
{
  protected $weight = 70;

  protected $columns = 2;

   protected $language = [
        'zh-cn' => [
            'title' => '本年销售详情',
            '1_month' => '1月',
            '2_month' => '2月',
            '3_month' => '3月',
            '4_month' => '4月',
            '5_month' => '5月',
            '6_month' => '6月',
            '7_month' => '7月',
            '8_month' => '8月',
            '9_month' => '9月',
            '10_month' => '10月',
            '11_month' => '11月',
            '12_month' => '12月',
        ],
        'en-us' => [
            'title' => 'Sales details of this year',
             '1_month' => 'January',
             '2_month' => 'February',
             '3_month' => 'March',
             '4_month' => 'April',
             '5_month' => 'May',
             '6_month' => 'June',
             '7_month' => 'July',
             '8_month' => 'August',
             '9_month' => 'September',
             '10_month' => 'October',
             '11_month' => 'November',
             '12_month' => 'December',
        ],
       'zh-hk' => [
           'title' => '本年銷售詳情',
           '1_month' => '1月',
           '2_month' => '2月',
           '3_month' => '3月',
           '4_month' => '4月',
           '5_month' => '5月',
           '6_month' => '6月',
           '7_month' => '7月',
           '8_month' => '8月',
           '9_month' => '9月',
           '10_month' => '10月',
           '11_month' => '11月',
           '12_month' => '12月',
       ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

  public function getData()
  {
    $data = (new IndexLogic())->thisYearSale();
    return $data;
  }

  public function output()
  {
    $content = '';
    $data = $this->getData();
    $title = $this->lang('title');

    $xMonthList = [];
    $yAmountList = [];
    foreach ($data['this_year_month_amount'] as $value) {
      $xMonthList[] = $this->lang( (string)$value['month'] . '_month');
      $yAmountList[] = $value['amount'];
    }
    $xMonthList = json_encode($xMonthList);
    $yAmountList = json_encode($yAmountList);
    $currencySuffix = configuration('currency_suffix');
    if (!empty($currencySuffix)) {
      $currencySuffix = '（' . $currencySuffix . '）';
    }

    return <<<HTML
     <div id="echars-box" class="echars-div"></div>

    <script>
      let XmonthList = JSON.parse('{$xMonthList}'), YamountList = JSON.parse('{$yAmountList}')
      // res.data.data.this_year_month_amount.forEach(item => {
      //   XmonthList.push(item.month + '月')
      //   YamountList.push(item.amount)
      // });
      let el = document.getElementById('echars-box')
      let myChart = echarts.init(el);
      const option = {
        title: {
          text: "{$title}{$currencySuffix}",
          left: 30,
          top: 26,
          textStyle: {
            fontSize: 18,
            fontWeight: 'bold',
            color: 'rgba(0, 0, 0, 0.9)'
          }
        },
        tooltip: {},
        color: '#0052D9',
        xAxis: {
          type: 'category',
          data: XmonthList
        },
        yAxis: {
          type: 'value'
        },
        grid: {
          left: "15%",
        },
        series: [{
          data: YamountList,
          type: 'bar'
        }]
      }
      myChart.setOption(option)
      window.addEventListener("resize", function () {
        myChart.resize()
      });
    </script>

HTML;
  }
}
