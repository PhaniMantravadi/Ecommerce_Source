<?php
/**
 * Sz PDF rewrite for custom attribute
 * * Attribute "sz_warehouse_location" has to be set manually
 * Original: Sales Order Invoice PDF model
 *
 * @category   Sz
 * @package    Sz_Invoice
 * @author    Sushil Zore - Sz <sushilzore@gmail.com>
 */
class Sz_Invoice_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
    private $_totalArray = array();
    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            $order = $invoice->getOrder();
            $supplierInfo = $this->getSupplierInfo($invoice->getId());
            /* Add image */
            if ($supplierInfo) {
                $this->insertSupplierHeader($page, $invoice->getStore(), $supplierInfo);
                $this->insertSupplierOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()), $supplierInfo, $invoice);
            } else {
                $this->insertLogo($page, $invoice->getStore());
                /* Add address */
                $this->insertAddress($page, $invoice->getStore());
                /* Add head */
                $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));
            }




            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);
            $page->drawText(Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId(), 35, 780, 'UTF-8');

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);

            $page->drawRectangle(25, $this->y, 570, $this->y -15);
            $this->y -=10;

            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $drawLinePoint = $this->y;
            $this->_setFontRegular($page,6);
            $page->drawText(Mage::helper('sales')->__('Qty'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Description'), 80, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price (Inc Tax)'), 160, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Gr Amt(Inc Tax)'), 225, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Discount'), 300, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Net Amt(Inc Tax)'), 350, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Type'), 425, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Rate'), 475, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Amt'), 520, $this->y, 'UTF-8');


            if ($supplierInfo) {
                $page->drawText('', 35, ($this->y -= 12), 'UTF-8');
            }
            $this->y -=15;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $totalTaxAmount = array();
            $allInvoiceItems = $invoice->getAllItems();
            $itemCount =  array();
            $totalTaxRate =  array();
            $indexVal = 0;
            $totalDiscountAmt =  array();
            $totalNetAmt =  array();

            $this->_setFontRegular($page,6);
            /* Add body */
            foreach ($allInvoiceItems as $item){

                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y < 15) {
                    $page = $this->newPage(array('table_header' => true));
                }
                $orderItemItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
                $item->setShowSku(true);
                $page = $this->_drawItem($item, $page, $order);
                $page->drawText('', 35, ($this->y -= 12), 'UTF-8');
                if ($orderItemItem->getSellerTaxType()) {
                    $index = trim(strtoupper($orderItemItem->getSellerTaxType()));
                    $this->_totalArray[$index]['tax_type'] = $index;
                } else {
                    $index = $indexVal++;
                    $this->_totalArray[$index]['tax_type'] = '';
                }
                if (!isset($totalTaxAmount[$index])) {
                    $totalTaxAmount[$index] = 0;
                }
                if (!isset($totalTaxRate[$index])) {
                    $totalTaxRate[$index] = 0;
                }
                if (!isset($totalDiscountAmt[$index])) {
                    $totalDiscountAmt[$index] = 0;
                }
                if (!isset($totalNetAmt[$index])) {
                    $totalNetAmt[$index] = 0;
                }
                if (!isset($itemCount[$index])) {
                    $itemCount[$index] = 0;
                }
                $totalTaxAmount[$index] = $totalTaxAmount[$index] + (($item->getRowTotal() * $orderItemItem->getSellerTaxRate())/100);
                $totalTaxRate[$index] = $totalTaxRate[$index] + $orderItemItem->getSellerTaxRate();
                $totalDiscountAmt[$index] = $totalDiscountAmt[$index] + $orderItemItem->getDiscountAmount();
                $totalNetAmt[$index] = $totalNetAmt[$index] + $orderItemItem->getRowTotal();
                $itemCount[$index]++;

                $this->_totalArray[$index]['total_tax_amount'] = $totalTaxAmount[$index];
                $this->_totalArray[$index]['total_tax_rate'] = $totalTaxRate[$index];
                $this->_totalArray[$index]['total_discount_amt'] = $totalDiscountAmt[$index];
                $this->_totalArray[$index]['total_net_amt'] = $totalNetAmt[$index];
                $this->_totalArray[$index]['item_count'] = $itemCount[$index];

                //$page->drawText('', 35, ($this->y -= 12), 'UTF-8');
            }

            if ($supplierInfo) {
                $page->drawText('', 35, ($this->y -= 12), 'UTF-8');
                $page->setLineWidth(0.5);
                $page->setLineDashingPattern(2);
                $page->drawLine(25,  $this->y, 570, $this->y);
                $this->drawHorizontalLines($drawLinePoint-5, $page, $this->y);
                $page = $this->insertSupplierTotals($page, $invoice);
            } else {
                /* Add totals */
                $page = $this->insertTotals($page, $invoice, $totalTaxAmount);
            }
            $this->_setFontRegular($page,10);
            if ($supplierInfo) {
                $page->drawText('', 35, ($this->y -= 12), 'UTF-8');
                $page->setLineWidth(0.5);
                $page->setLineDashingPattern(2);
                $page->drawLine(25,  $this->y, 570, $this->y);
                $page->drawLine(25,  $this->y-10, 570, $this->y-10);

                $this->getAuthorizeBlock(
                    $page, $supplierInfo
                );
                $page->setLineWidth(0.5);
                $page->setLineDashingPattern(2);
                $page->drawLine(25,  $this->y, 570, $this->y);
                $supplierAddress = $this->_formatAddress($supplierInfo->getPrimaryBillingAddress()->format('pdf'));
                unset($supplierAddress[0]);
                $registeredAddress = implode(',', $supplierAddress);
                $this->_setFontRegular($page, 6);
                $page->drawText(
                    Mage::helper('sales')->__('Registered Office Address: ').$registeredAddress, 35, ($this->y -= 12), 'UTF-8');
            }
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }
        $this->_afterGetPdf();

        return $pdf;
    }

    public function drawHorizontalLines($intialPoint = 0, $page, $end = 0) {
        $page->drawLine(57,  $intialPoint, 57, $end);
        $page->drawLine(153,  $intialPoint, 153, $end);
        $page->drawLine(210,  $intialPoint, 210, $end);
        $page->drawLine(280,  $intialPoint, 280, $end);
        $page->drawLine(340,  $intialPoint, 340, $end);
        $page->drawLine(410,  $intialPoint, 410, $end);
        $page->drawLine(465,  $intialPoint, 465, $end);
        $page->drawLine(510,  $intialPoint, 510, $end);
        return $page;
    }
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;

            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $this->_setFontRegular($page, 6);
            $page->drawText(Mage::helper('sales')->__('Qty'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Description'), 80, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price (Inc Tax)'), 160, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Gr Amt(Inc Tax)'), 225, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Discount'), 300, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Net Amt(Inc Tax)'), 350, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Type'), 425, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Rate'), 475, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax Amt'), 520, $this->y, 'UTF-8');

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -=20;
        }
        return $page;
    }

    /**
     * Insert totals to pdf page
     *
     * @param  Zend_Pdf_Page $page
     * @param  Mage_Sales_Model_Abstract $source
     * @return Zend_Pdf_Page
     */
    protected function insertSupplierTotals($page, $source){
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
        $this->_setFontRegular($page,6);
        $page->drawText('', 35, ($this->y -= 12), 'UTF-8');
        $page->drawText('', 35, $this->y, 'UTF-8');
        $page->drawText('', 60, $this->y, 'UTF-8');
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -=10;

        /* Add table head */
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
        $page->drawText(Mage::helper('sales')->__('Total Gr Amt'), 225, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Total Discount'), 285, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Final Net Amt'), 355, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Tax Type'), 425, $this->y, 'UTF-8');
        //$page->drawText(Mage::helper('sales')->__('Tax Rate'), 475, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Total Tax Amt'), 520, $this->y, 'UTF-8');
        $this->y-=30;
        $order = $source->getOrder();
        foreach ($this->_totalArray as $key => $value) {
            $page->drawText($order->formatPriceTxt($value['total_net_amt']), 230, $this->y, 'UTF-8');
            $page->drawText($order->formatPriceTxt($value['total_discount_amt']), 290, $this->y, 'UTF-8');
            $page->drawText($order->formatPriceTxt($value['total_net_amt']-$value['total_discount_amt']), 360, $this->y, 'UTF-8');
            $page->drawText($value['tax_type'], 435, $this->y, 'UTF-8');
            //$page->drawText($value['total_tax_rate']/$value['item_count'].'%', 480, $this->y, 'UTF-8');
            $page->drawText($order->formatPriceTxt($value['total_tax_amount']), 525, $this->y, 'UTF-8');
            $this->y -=10;
        }

        return $page;
    }


    public function getAuthorizeBlock(&$page, $supplierInfo) {
        $termsAndConditions = Mage::getStoreConfig('marketplace_invoice/supplier_invoice/terms_condition');
        $stateName = Mage::getModel('directory/region')->load(
            $supplierInfo->getPrimaryShippingAddress()->getRegionId()
        );
        $termsAndConditions = str_replace('{{STATE}}', $stateName->getName(), $termsAndConditions);
        $termsAndConditions = preg_split('/\r\n|[\r\n]/', $termsAndConditions);
        $this->y = $this->y ? $this->y : 815;
        $initialPoint = $this->y;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 6);
        $page->drawText('', 35, ( $this->y), 'UTF-8');
        $this->y -= 10;
        $page->drawText('', 35, ( $this->y), 'UTF-8');
        $this->y -= 10;
        $top = $this->y;
        $signatureYPosition =  $this->y-100;
        foreach ($termsAndConditions as $message) {
            $page->drawText($message, 35, ( $this->y), 'UTF-8');
            $this->y -= 10;
        }
        $page->drawText(Mage::helper('sales')->__('For ').$supplierInfo->getSupplierInvoiceAuthLabel(),
            $this->getAlignRight($supplierInfo->getData('company_name'), 80, 400, $this->_setFontRegular($page, 10), 10),
            $top,
            'UTF-8');
        $this->insertSupplierSign($page, $supplierInfo, $signatureYPosition);
        $page->drawLine(380,  $initialPoint-10, 380, $this->y);
    }

    public function getSupplierInfo($invoiceId = null) {
        try {
            if (is_null($invoiceId)) {
                return false;
            }
            $supplierInfo = '';
            $supplierCollection = Mage::getModel('marketplace/invoice')->getCollection();
            $supplierCollection->getSelect()->where('invoice_id = '.$invoiceId);
            if (count($supplierCollection)) {
                foreach ($supplierCollection as $supplier) {
                    $supplierInfo = Mage::getModel('customer/customer')->load($supplier->getData('supplier_id'));
                }
            }
            return $supplierInfo;
        } catch (Exception $e) {
            Mage::log($e, null, 'supplier_invoice.log', true);
            return false;
        }
    }

    /**
     * Insert logo to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
     */
    protected function insertSupplierSign(&$page, $supplierInfo, $y)
    {
        $image = Mage::helper('marketplace')->getSupplierInvoiceSign($supplierInfo->getId());
        if ($image) {
            $image = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;
            $image  .= $supplierInfo->getSupplierInvoiceAuthSign();
            if (is_file($image)) {
                $image       = Zend_Pdf_Image::imageWithPath($image);
                $top         = 830; //top border of the page
                $width       = 120;
                $height      = 60;

                $y1 = $y+50;
                $y2 =  $y + $height + 20;
                $x1 = 400;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);
            }
        }
    }

    /**
     * Insert logo to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
     */
    protected function insertSupplierHeader(&$page, $store = null, $supplierInfo)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = Mage::helper('marketplace')->getSupplierInvoiceHeaderLogo($supplierInfo->getId());
        $this->_setFontBold($page, 12);
        $page->drawText(
            Mage::helper('sales')->__('Retail/TaxInvoice/Cash Memorandum') , 35, $this->y-30, 'UTF-8'
        );
        if ($image) {
            $image = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;
            $image  .= $supplierInfo->getSupplierInvoiceLogo();
            if (is_file($image)) {
                $image       = Zend_Pdf_Image::imageWithPath($image);
                $top         = 830; //top border of the page
                $width       = 120;
                $height      = 60;
                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 450;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
        $page->drawText('', 35, ( $this->y), 'UTF-8');
        $this->_setFontBold($page, 8);
    }

    /**
     * Insert address to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
     */
    protected function insertSupplierAddress(&$page, $store = null, $supplierInfo)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 815;
        $supplierAddress = $this->_formatAddress($supplierInfo->getPrimaryShippingAddress()->format('pdf'));
        foreach ($supplierAddress as $_value) {
            $page->drawText(trim(strip_tags($_value)),
                $this->getAlignRight($_value, 130, 440, $font, 10),
                $top,
                'UTF-8');
            $top -= 10;
        }
        $this->y = ($this->y > $top) ? $top : $this->y;
    }
    protected function insertSupplierOrder(&$page, $obj, $putOrderId = true, $supplierInfo, $invoice)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;
        $supplierInfoHeight = $this->_calcSupplierInfoHeight($supplierInfo);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.85));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
        //$page->drawRectangle(25, $top, 570, $top - 55);
        $page->setLineWidth(0.5);
        $page->setLineDashingPattern(2);
        $page->drawLine(25,  $top, 570, $top);

        $page->setLineWidth(1);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->setDocHeaderCoordinates(array(25, $top, 570, $top));
        $this->_setFontRegular($page, 8);
        $this->_setFontBold($page, 8);
        if ($putOrderId) {
            $page->drawText(
                Mage::helper('sales')->__('Order No:'), 35, ($top -= 12), 'UTF-8'
            );
            $this->_setFontBold($page, 7);
            $page->drawText(
               $order->getRealOrderId(), 85, $top, 'UTF-8'
            );
            $page->drawText('', 35, ($top -= 8), 'UTF-8');
            $this->_setFontBold($page, 8);
            $page->drawText(
                Mage::helper('sales')->__('Date: '), 35, ($top -= 12), 'UTF-8'
            );
            $this->_setFontBold($page, 7);
            $page->drawText(
                Mage::helper('core')->formatDate(
                    $order->getCreatedAtStoreDate(), 'medium', false
                ), 85, $top, 'UTF-8'
            );
            $this->_setFontBold($page, 8);
        }
        $page->drawText('', 35, ($top -= 8), 'UTF-8');

        $this->_setFontRegular($page, 8);
        $this->_setFontBold($page, 8);
        $page->drawText(Mage::helper('sales')->__('Sold By:'), 35, ($top -= 12), 'UTF-8');

        $page->drawText('', 35, ( $this->y), 'UTF-8');
        $page->drawText('', 35, ( $this->y), 'UTF-8');
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 8);
        $this->_setFontBold($page, 7);
        $supplierAddress = $this->_formatAddress($supplierInfo->getPrimaryBillingAddress()->format('pdf'));
        foreach ($supplierAddress as $_value) {
            $page->drawText(trim(strip_tags($_value)), 35, ($top -= 12), 'UTF-8');
        }
        $this->_setFontRegular($page, 10);
        $page->drawText('', 35, ($top -= 12), 'UTF-8');
        $this->_setFontBold($page, 8);
        $page->drawText(Mage::helper('sales')->__('VAT/TIN Number:'), 35, ($top -= 12), 'UTF-8');
        $this->_setFontBold($page, 7);
        $page->drawText($supplierInfo->getData('vat'), 120, $top, 'UTF-8');
        $this->_setFontBold($page, 8);
        $page->drawText(Mage::helper('sales')->__('CST Number :'), 35, ($top -= 12), 'UTF-8');
        $this->_setFontBold($page, 7);
        $page->drawText($supplierInfo->getData('cst'), 120, $top, 'UTF-8');

        $this->_setFontBold($page, 8);
        $page->drawText(Mage::helper('sales')->__('Invoice Number : '),
            $this->getAlignRight($invoice->getIncrementId(), 80, 400, $this->_setFontRegular($page, 8), 10),
            $top,
            'UTF-8');
        $page->drawText($invoice->getIncrementId(),
            $this->getAlignRight($invoice->getIncrementId(), 80, 470, $this->_setFontRegular($page, 7), 10),
            $top,
            'UTF-8');
        $top -= 10;

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, ($top - 20));
        $page->drawRectangle(275, $top, 570, ($top - 20));

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 8);
        $page->drawText(Mage::helper('sales')->__('Billing Address:'), 35, ($top - 12), 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('Shipping Addres:'), 285, ($top - 12), 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;
        $this->_setFontBold($page, 6);
        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 10;
                }
            }
        }


        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $text = array();
                    foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }

        }
        $page->drawText('', 35, $this->y -= 30, 'UTF-8');
    }

    /**
     * Calculate address height
     *
     * @param  array $address
     * @return int Height
     */
    protected function _calcSupplierInfoHeight($supplierInfo)
    {
        $y = 0;
        $supplierAddress = $this->_formatAddress($supplierInfo->getPrimaryBillingAddress()->format('pdf'));
        foreach ($supplierAddress as $value){
            if ($value !== '') {
                $text = array(
                    'first Line',
                    'Second Line',
                    'Third Line'
                );
                foreach (Mage::helper('core/string')->str_split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }
}