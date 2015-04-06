<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DateTime;

/**
 * Helper for convert date to age
 */
class relativeTime extends AbstractHelper
{

    /**
     * Convert date to age
     *
     * @param  mixed $data
     * @return string
     */
    
    public function __invoke($value)
    {
        $from = time();
        
        $time = new DateTime($value);
        
        $time = $from - $time->getTimestamp();
 
        $chunks = array(
            array(60 * 60 * 24 * 365 , 'ans'),
            array(60 * 60 * 24 * 30 , 'mois'),
            array(60 * 60 * 24 , 'jour'),
            array(60 * 60 , 'hour'),
            array(60 , 'minute'),
            array(1 , 'seconde')
        );
 
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            if (($count = floor($time / $seconds)) != 0) {
                break;
            }
        }
 
        $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
        
        return $print;
    }
}
