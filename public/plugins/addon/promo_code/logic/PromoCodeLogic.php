<?php
namespace addon\promo_code\logic;

use addon\promo_code\model\PromoCodeModel;

class PromoCodeLogic
{

    public function generatePromoCode()
    {

        $promoCode = rand_str(9);
        
        $match = 0;
        if(preg_match('/[0-9]/',$promoCode)){
            $match += 1;
        }
        if(preg_match('/[a-z]/',$promoCode)){
            $match += 1;
        }
        if(preg_match('/[A-Z]/',$promoCode)){
            $match += 1;
        }
        if($match<3){
            $this->generatePromoCode();
        }

        $PromoCodeModel = new PromoCodeModel();

        $exist = $PromoCodeModel->where('code',$promoCode)->find();

        if ($exist){
            $this->generatePromoCode();
        }

        return $promoCode;
    }

}
