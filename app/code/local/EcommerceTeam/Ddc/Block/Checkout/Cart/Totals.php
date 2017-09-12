<?php
/**
 *  rewrite	Mage_Checkout_Block_Cart_Totals
 */

class EcommerceTeam_Ddc_Block_Checkout_Cart_Totals  extends Mage_Checkout_Block_Cart_Totals{

     /**
     * Render totals html for specific totals area (footer, body)
     *
     * @param   null|string $area
     * @param   int $colspan
     * @return  string
     */
    public function renderTotals($area = null, $colspan = 1)
    {
       // $notShowTotalsHere=array('shipping','deliveryfee');
        $html = '';
        $subtotal=0;

        foreach($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
            //if($this->_isAllowed() && in_array($total->getCode(),$notShowTotalsHere))continue;
            
            if($total->getValue()==0.00){
                continue;
            }
            
            $html .= $this->renderTotal($total, $area, $colspan);
            Mage::log('html:--| '.$total->getTitle().' | '.$total->getValue(),null,'ado.txt');
        }



        return $html;
    }
    

    
}
?>
