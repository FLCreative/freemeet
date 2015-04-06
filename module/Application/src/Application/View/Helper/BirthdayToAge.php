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
class BirthdayToAge extends AbstractHelper
{

    /**
     * Convert date to age
     *
     * @param  mixed $data
     * @return string
     */
    
    public function __invoke($value)
    {
        if(!$value instanceof DateTime)
        {
            $value = new DateTime($value);
        }
        
        $oDateNow = new DateTime();
        $oDateIntervall = $oDateNow->diff($value);
        
        return $oDateIntervall->y;
    }
}
