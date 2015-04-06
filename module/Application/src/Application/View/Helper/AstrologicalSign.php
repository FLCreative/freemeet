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
class astrologicalSign extends AbstractHelper
{

    /**
     * Convert date to age
     *
     * @param  mixed $data
     * @return string
     */
    
    public function __invoke($value)
    {
		$mois_jour = substr($value, 5, 2).substr($value, 8, 2);
		
		$tab_date_signe = array(
			'0120' => 'Capricorne',
			'0218' => 'Verseau',
			'0320' => 'Poisson',
			'0420' => 'Bélier',
			'0521' => 'Taureau',
			'0621' => 'Gémeaux',
			'0722' => 'Cancer',
			'0822' => 'Lion',
			'0922' => 'Vierge',
			'1022' => 'Balance',
			'1122' => 'Scorpion',
			'1221' => 'Sagittaire',
			'1300' => 'Capricorne',
		);
		
		foreach ($tab_date_signe as $cle => $valeur)
		{
			if ($mois_jour < $cle)
			{
					return $valeur;
			}
		}
    }
}
