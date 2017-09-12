<?php class Magebird_Popup_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    protected function brightness($color, $inc)
    {
        $color = str_replace('#', '', $color);
        $r = substr($color, 0, 2);
        $g = substr($color, 2, 2);
        $b = substr($color, 4, 2);
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        $r = max(0, min(255, $r + $inc));
        $g = max(0, min(255, $g + $inc));
        $b = max(0, min(255, $b + $inc));
        $dr = dechex($r);
        if (strlen($dr) == 1) $dr = "0" . $dr;
        $dg = dechex($g);
        if (strlen($dg) == 1) $dg = "0" . $dg;
        $db = dechex($b);
        if (strlen($db) == 1) $db = "0" . $db;
        return '#' . $dr . $dg . $db;
    }
} ?>