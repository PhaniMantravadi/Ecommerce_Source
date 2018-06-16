<?php
/**
 * Sz PDF rewrite for custom attribute
 * Attribute "inchoo_warehouse_location" has to be set manually
 * Original: Sales Order Invoice Pdf default items renderer
 *
 * @category   Sz
 * @package    Sz_Invoice
 * @author    Sushil Zore - Sz <sushilzore@gmail.com>
 */

class Sz_Invoice_Model_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Invoice_Default
{

    /**
     * Draw item line
     **/
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        $orderItemItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
        $fontSize = 6;
        if ($item->getShowSku()) {
            // draw SKU
            $lines[0][] = array(
                'text' => Mage::helper('core/string')->str_split('SKU :'.$this->getSku($item), 60, true, true),
                'feed'  => 60,
                'font_size' => $fontSize,
            );
        } else {
            // draw qty
            $lines[0] = array(array(
                'text'  => $item->getQty()*1,
                'feed' => 35,
                'font_size' => $fontSize
            ));

            $description = Mage::helper('core/string')->str_split($item->getName(), 27, true, true);
            // draw Description
            $lines[0][] = array(
                'text' => $description,
                'feed'  => 60,
                'font_size' => $fontSize,
                'height' => 3 * count($description)
            );


            $taxAmount = $this->calculateTaxAmount(
                $item->getRowTotal(),
                str_replace('%', '',$orderItemItem->getSellerTaxRate())
            );
            // draw Product Price
            $lines[0][] = array(
                'text'  => $order->formatPriceTxt($item->getPrice()),
                'feed'  => 160,
                'font_size' => $fontSize
            );

            // draw Grand Total
            $lines[0][] = array(
                'text'  => $order->formatPriceTxt($item->getRowTotal()),
                'feed'  => 225,
                'font_size' => $fontSize
            );

            // draw Discount
            $lines[0][] = array(
                'text'  => $order->formatPriceTxt($orderItemItem->getDiscountAmount()),
                'feed'  => 295,
                'font_size' => $fontSize
            );

            // draw Net Amount
            $lines[0][] = array(
                'text'  => $order->formatPriceTxt($item->getRowTotal()-$orderItemItem->getDiscountAmount()),
                'feed'  => 365,
                'font_size' => $fontSize
            );

            // draw Tax Type
            $lines[0][] = array(
                'text'  => strtoupper($orderItemItem->getSellerTaxType()),
                'feed'  => 430,
                'font_size' => $fontSize
            );

            // draw Tax Rate
            $lines[0][] = array(
                'text'  => (float)str_replace('%', '',$orderItemItem->getSellerTaxRate()).'%',
                'feed'  => 480,
                'font_size' => $fontSize
            );


            // draw Tax Amount(Included in Net)
            $lines[0][] = array(
                'text'  => $order->formatPriceTxt($taxAmount),
                'feed'  => 530,
                'font_size' => $fontSize
            );


            // custom options
            $options = $this->getItemOptions();
            if ($options) {
                foreach ($options as $option) {
                    // draw options label
                    $lines[][] = array(
                        'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                        'font' => 'italic',
                        'feed' => 35
                    );

                    if ($option['value']) {
                        $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            $lines[][] = array(
                                'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
                                'feed' => 40
                            );
                        }
                    }
                }
            }
        }




        $lineBlock = array(
            'lines'  => $lines,
            'height' => 3,
            'font_size' => $fontSize
        );
        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));

        $this->setPage($page);

    }


    public function calculateTaxAmount($price = null, $taxRate = null) {
        try {
            $taxAmount = 0;
            if (!is_null($price) && !is_null($taxRate)) {
                $taxAmount = ($price * $taxRate)/100;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $taxAmount;
    }
}