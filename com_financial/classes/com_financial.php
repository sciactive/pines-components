<?php
/**
 * com_financial class.
 *
 * @package Components
 * @subpackage financial
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @author Enrique Garc�a M. <egarcia@egm.as>
 * @copyright SciActive.com
 * @copyright (c) 2003-2004 Enrique Garc�a M.
 * @link http://sciactive.com/
 */
/*
 * EGM Mathematical Finance class.
 *
 * Financial functions with the Excel function names and
 * parameter order.
 *
 * @version   $Id: financial_class.php,v 1.0.6 2004-06-30 13:20:56-05 egarcia Exp $
 * @author    Enrique Garc�a M. <egarcia@egm.as>
 * @copyright (c) 2003-2004 Enrique Garc�a M.
 * @since     Saturday, January 7, 2003
 **/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/**
 * Constants to define the accuracy in numeric aproximations, and the max
 * iterations to solve
 * @package Components
 * @subpackage financial
 */
define('COM_FINANCIAL_ACCURACY', 1.0e-6);
define('COM_FINANCIAL_MAX_ITERATIONS', 100);

define('COM_FINANCIAL_SECS_PER_DAY', 24 * 60 * 60);
define('COM_FINANCIAL_HALF_SEC', 0.5 / COM_FINANCIAL_SECS_PER_DAY);

/**
 * Constants for the day-counting
 * rules are mandated by NASD Uniform Practice Code, Section 46, and MSRB Rule G-33,
 * described in Mayle (1993, 17?23, A3?A15). See Public Securities Association (1990)
 * for other rules applicable to state and municipal bonds.
 *
 * base 0: BASIS_MSRB_30_360
 *         MSRB/NSAD 30/360 daycount method
 *         see MSRB Rule G-33
 *         http://www.msrb.org/msrb1/rules/ruleg33.htm
 *         http://www.msrb.org/msrb1/rules/interpg33.htm
 *         Number of days = (Y2 - Y1) 360 + (M2 - M1) 30 + (D2 - D1)
 *         The variables "Yl," "M1," and "D1" are defined as the year, month, and day, respectively,
 *         of the date on which the computation period begins (June 15, 1982, in your example),
 *         and "Y2,a" "M2," and "D2" as the year, month, and day of the date on which the
 *         computation period ends (July 1, 1982, in your example).
 *         For purposes of this formula, if the symbol "D2" has a value of "31," and the symbol "D1"
 *         has a value of "30" or "31," the value of the symbol "D2" shall be changed to "30."
 *         If the symbol "D1" has a value of "31," the value of the symbol "D1" shall be changed to
 *         "30." For purposes of this rule time periods shall be computed to include the day
 *         specified in the rule for the beginning of the period but not to include the day
 *         specified for the end of the period.
 *
 * base 1: BASIS_ACTACT
 *         Actual/Actual daycount method
 *         date adjustment: no change
 *         date difference: serial delta (# of days)
 *
 * base 2: BASIS_ACT_360
 *         Actual/360 daycount method
 *         date adjustment: no change
 *         date difference: serial delta
 *         360/freq for length of coupon period
 *
 * base 3: BASIS_ACT_365
 *         Actual/365 daycount method (short term and Canadian bonds only)
 *         date adjustment: no change
 *         date difference: serial delta
 *         365/freq for length of coupon period, (with decimal answer)
 * base 4: BASIS_30E_360
 *         date adjustment: from_date is changed from 31st to 30th
 *                          to_date is changed from 31st to 30th
 *         date difference: each month 30 days, within a month serial delta
 * base 5: BASIS_30Ep_360
 *         date adjustment: from_date is changed from 31st to 30th
 *                          to_date is changed from 31st to 1st of following month
 *         date difference: each month 30 days, within a month serial delta
 * @package Components
 * @subpackage financial
 */
define('COM_FINANCIAL_BASIS_MSRB_30_360', 0);
define('COM_FINANCIAL_BASIS_ACT_ACT', 1);
define('COM_FINANCIAL_BASIS_ACT_360', 2);
define('COM_FINANCIAL_BASIS_ACT_365', 3);
define('COM_FINANCIAL_BASIS_30E_360', 4);
define('COM_FINANCIAL_BASIS_30Ep_360', 5);

/**
 * com_financial main class.
 *
 * Financial functions.
 *
 * @package Components
 * @subpackage financial
 */
class com_financial extends component {
	function __construct() {
		// forces the precision for calculations
		ini_set('precision', '14');
	}

	/**
	* DATEADD
	* Returns a new Unix timestamp value based on adding an interval to the specified date.
	* @param  string  $datepart is the parameter that specifies on which part of the date to return a new value.
	* @param  integer $number   is the value used to increment datepart. If you specify a value that is not an integer, the fractional part of the value is discarded.
	* @param  integer $date     a Unix timestamp value.
	* @return integer a Unix timestamp.
	*/
	function DATEADD($datepart, $number, $date) {
		$number = intval($number);
		switch (strtolower($datepart)) {
			case 'yy':
			case 'yyyy':
			case 'year':
				$d = getdate($date);
				$d['year'] += $number;
				if (($d['mday'] == 29) && ($d['mon'] == 2) && (date('L', mktime(0, 0, 0, 1, 1, $d['year'])) == 0)) $d['mday'] = 28;
				return mktime($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], $d['mday'], $d['year']);
				break;
			case 'm':
			case 'mm':
			case 'month':
				$d = getdate($date);
				$d['mon'] += $number;
				while($d['mon'] > 12) {
					$d['mon'] -= 12;
					$d['year']++;
				}
				while($d['mon'] < 1) {
					$d['mon'] += 12;
					$d['year']--;
				}
				$l = date('t', mktime(0,0,0,$d['mon'],1,$d['year']));
				if ($d['mday'] > $l) $d['mday'] = $l;
				return mktime($d['hours'], $d['minutes'], $d['seconds'], $d['mon'], $d['mday'], $d['year']);
				break;
			case 'd':
			case 'dd':
			case 'day':
				return ($date + $number * 86400);
				break;
			default:
				die("Unsupported operation");
		}
	}

	/**
	* DATEDIFF
	* Returns the number of date and time boundaries crossed between two specified dates.
	* @param  string  $datepart  is the parameter that specifies on which part of the date to calculate the difference.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return integer the number between the two dates.
	*/
	function DATEDIFF($datepart, $startdate, $enddate) {
		switch (strtolower($datepart)) {
			case 'yy':
			case 'yyyy':
			case 'year':
				$di = getdate($startdate);
				$df = getdate($enddate);
				return $df['year'] - $di['year'];
				break;
			case 'q':
			case 'qq':
			case 'quarter':
				die("Unsupported operation");
				break;
			case 'n':
			case 'mi':
			case 'minute':
				return ceil(($enddate - $startdate) / 60);
				break;
			case 'hh':
			case 'hour':
				return ceil(($enddate - $startdate) / 3600);
				break;
			case 'd':
			case 'dd':
			case 'day':
				return ceil(($enddate - $startdate) / 86400);
				break;
			case 'wk':
			case 'ww':
			case 'week':
				return ceil(($enddate - $startdate) / 604800);
				break;
			case 'm':
			case 'mm':
			case 'month':
				$di = getdate($startdate);
				$df = getdate($enddate);
				return ($df['year'] - $di['year']) * 12 + ($df['mon'] - $di['mon']);
				break;
			default:
				die("Unsupported operation");
		}
	}

	/**
	* Determine if the basis is valid
	* @param  integer $basis
	* @return bool
	*/
	function _is_valid_basis($basis) {
		return (($basis >= COM_FINANCIAL_BASIS_MSRB_30_360) && ($basis <= COM_FINANCIAL_BASIS_30Ep_360));
	}

	/**
	* Determine if the frequency is valid
	* @param  integer $frequency
	* @return bool
	*/
	function _is_valid_frequency($frequency) {
		return (($frequency == 1) || ($frequency == 2) || ($frequency == 4));
	}

	/**
	* DAYS360
	* Returns the number of days between two dates based on a 360-day year
	* (twelve 30-day months), which is used in some accounting calculations.
	* Use this function to help compute payments if your accounting system
	* is based on twelve 30-day months.
	* @param  integer $start_date is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $end_date   is the ending date (Unix timestamp) for the calculation.
	* @param  bool    $method     is a logical value that specifies whether to use the U.S. or European method in the calculation.
	* @return integer the number of days between two dates based on a 360-day year
	*/
	function DAYS360($start_date, $end_date, $method = false) {
		if ($method) {
			return $this->Thirty360USdayCount($start_date, $end_date);
		} else {
			return $this->Thirty360EUdayCount($start_date, $end_date);
		}
	}

	/**
	* Thirty360USdayCount
	* Returns the number of days between two dates based on a 360-day year
	* (twelve 30-day months) using the US method.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return integer the number of days between two dates
	*/
	function Thirty360USdayCount($startdate, $enddate) {
		$d1 = getdate($startdate);
		$d2 = getdate($enddate);
		$dd1 = $d1['mday']; $mm1 = $d1['mon']; $yy1 = $d1['year'];
		$dd2 = $d2['mday']; $mm2 = $d2['mon']; $yy2 = $d2['year'];

		if ($dd2 == 31 && $dd1 < 30) { $dd2 = 1; $mm2++; }

		return 360 * ($yy2 - $yy1) + 30 * ($mm2 - $mm1 - 1) + max(0, 30 - $dd1) + min(30, $dd2);
	}

	/**
	* Thirty360USyearFraction
	* Returns the period between two dates as a fraction of year.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return float   the fraction of years between two dates
	*/
	function Thirty360USyearFraction($startdate, $enddate) {
		return $this->Thirty360USdayCount($startdate, $enddate) / 360.0;
	}

	/**
	* Thirty360EUdayCount
	* Returns the number of days between two dates based on a 360-day year
	* (twelve 30-day months) using the EUROPEAN method.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return integer the number of days between two dates
	*/
	function Thirty360EUdayCount($startdate, $enddate) {
		$d1 = getdate($startdate);
		$d2 = getdate($enddate);
		$dd1 = $d1['mday']; $mm1 = $d1['mon']; $yy1 = $d1['year'];
		$dd2 = $d2['mday']; $mm2 = $d2['mon']; $yy2 = $d2['year'];

		return 360 * ($yy2 - $yy1) + 30 * ($mm2 - $mm1 - 1) + max(0, 30 - $dd1) + min(30, $dd2);
	}

	/**
	* Thirty360EUyearFraction
	* Returns the period between two dates as a fraction of year.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return float   the fraction of years between two dates
	*/
	function Thirty360EUyearFraction($startdate, $enddate) {
		return $this->Thirty360EUdayCount($startdate, $enddate) / 360.0;
	}

	/**
	* ActualActualdayCount
	* Returns the number of days between two dates.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return integer the number of days between two dates
	*/
	function ActualActualdayCount($startdate, $enddate) {
		return $this->DATEDIFF('day', $startdate, $enddate);
	}

	/**
	* ActualActualyearFraction
	* Returns the period between two dates as a fraction of year.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @param  date    $refPeriodStart is the reference beginning date (Unix timestamp) for the inner calculation.
	* @param  date    $refPeriodEnd   is the reference ending date (Unix timestamp) for the inner calculation.
	* @return float   the fraction of years between two dates
	*/
	function ActualActualyearFraction($startdate, $enddate, $refPeriodStart = null, $refPeriodEnd = null) {
		$t = time();
		if (!isset($refPeriodStart)) $refPeriodStart = $startdate;
		if (!isset($refPeriodEnd)) $refPeriodEnd = $enddate;

		/*
		if ($this->DATEDIFF('day', $startdate, $enddate) == 0) return 0.0;
		$d1 = getdate($startdate);
		$d2 = getdate($enddate);
		$dib1 = ((date('L', $startdate) == 1) ? 366.0 : 365.0);
		$dib2 = ((date('L', $enddate) == 1) ? 366.0 : 365.0);

		$sum = $d2['year'] - $d1['year'] - 1;
		$sum += $this->ActualActualdayCount($startdate, mktime(0,0,0,1,1,$d1['year'] + 1)) / $dib1;
		$sum += $this->ActualActualdayCount(mktime(0,0,0,1,1,$d2['year']), $enddate) / $dib2;
		return $sum;
		*/

		if ($this->DATEDIFF('day', $startdate, $enddate) == 0) return 0.0;

		if ($startdate > $enddate) die("Invalid dates");
		if (!($refPeriodStart != $t) && ($refPeriodEnd != $t) &&
			($refPeriodEnd > $refPeriodStart) && ($refPeriodEnd > $startdate)) die("Invalid reference period");

		$months = intval(0.5 + 12 * $this->DATEDIFF('day', $refPeriodStart, $refPeriodEnd) / 365);
		$period = $months / 12.0;
		if($months == 0) die("number of months does not divide 12 exactly");
		if ($enddate <= $refPeriodEnd) {
			// here refPeriodEnd is a future (notional?) payment date
			if ($startdate >= $refPeriodStart) {
				// here refPeriodStart is the last (maybe notional)
				// payment date.
				// refPeriodStart <= startdate <= enddate <= refPeriodEnd
				// [maybe the equality should be enforced, since
				// refPeriodStart < startdate <= enddate < refPeriodEnd
				// could give wrong results] ???
				return $period * $this->ActualActualdayCount($startdate, $enddate) /
					$this->ActualActualdayCount($refPeriodStart, $refPeriodEnd);
			} else {
				// here refPeriodStart is the next (maybe notional)
				// payment date and refPeriodEnd is the second next
				// (maybe notional) payment date.
				// startdate < refPeriodStart < refPeriodEnd
				// AND enddate <= refPeriodEnd
				// this case is long first coupon
				// the last notional payment date
				$previousRef = $this->DATEADD('month', -$months, $refPeriodStart);
				if ($enddate > $refPeriodStart)
					return $this->ActualActualyearFraction($startdate, $refPeriodStart, $previousRef,
								$refPeriodStart) +
							$this->ActualActualyearFraction($refPeriodStart, $enddate, $refPeriodStart,
								$refPeriodEnd);
				else
					return $this->ActualActualyearFraction($startdate,$enddate,$previousRef,$refPeriodStart);
			}
		} else {
			// here refPeriodEnd is the last (notional?) payment date
			// startdate < refPeriodEnd < enddate AND refPeriodStart < refPeriodEnd
			if ($refPeriodStart > $startdate) die("Invalid dates");

			// now it is: refPeriodStart <= startdate < refPeriodEnd < enddate

			// the part from startdate to refPeriodEnd
			$sum = $this->ActualActualyearFraction($startdate, $refPeriodEnd, $refPeriodStart, $refPeriodEnd);

			// the part from refPeriodEnd to enddate
			// count how many regular periods are in [refPeriodEnd, enddate],
			// then add the remaining time
			$i = 0;
			do {
				$newRefStart = $this->DATEADD('month', $months * $i, $refPeriodEnd);
				$newRefEnd   = $this->DATEADD('month', $months * ($i + 1), $refPeriodEnd);
				if ($enddate < $newRefEnd) {
					break;
				} else {
					$sum += $period;
					$i++;
				}
			} while (true);
			$sum += $this->ActualActualyearFraction($newRefStart, $newRefEnd, $newRefStart, $newRefEnd);
			return $sum;
		}
	}

	/**
	* Actual360dayCount
	* Returns the number of days between two dates.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return integer the number of days between two dates
	*/
	function Actual360dayCount($startdate, $enddate) {
		return $this->DATEDIFF('day', $startdate, $enddate);
	}

	/**
	* Actual360yearFraction
	* Returns the period between two dates as a fraction of 360 days year.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return float   the fraction of years between two dates
	*/
	function Actual360yearFraction($startdate, $enddate) {
		return $this->Actual360dayCount($startdate, $enddate) / 360.0;
	}

	/**
	* Actual365dayCount
	* Returns the number of days between two dates.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return integer the number of days between two dates
	*/
	function Actual365dayCount($startdate, $enddate) {
		return $this->DATEDIFF('day', $startdate, $enddate);
	}

	/**
	* Actual365yearFraction
	* Returns the period between two dates as a fraction of 365 days year.
	* @param  integer $startdate is the beginning date (Unix timestamp) for the calculation.
	* @param  integer $enddate   is the ending date (Unix timestamp) for the calculation.
	* @return float   the fraction of years between two dates
	*/
	function Actual365yearFraction($startdate, $enddate) {
		return $this->Actual365dayCount($startdate, $enddate) / 365.0;
	}

	/**
	* DOLLARDE
	* Converts a dollar price expressed as a fraction into a dollar
	* price expressed as a decimal number. Use DOLLARDE to convert
	* fractional dollar numbers, such as securities prices, to decimal
	* numbers.
	* @param  float   $fractional_dollar is a number expressed as a fraction.
	* @param  integer $fraction          is the integer to use in the denominator of the fraction.
	* @return float   dollar price expressed as a decimal number.
	*/
	function DOLLARDE($fractional_dollar, $fraction) {
		$fraction = intval($fraction);
		$integer = intval($fractional_dollar);
		return $integer + 100 * ($fractional_dollar - $integer) / $fraction;
	}

	/**
	* DOLLARFR
	* Converts a dollar price expressed as a decimal number into a
	* dollar price expressed as a fraction. Use DOLLARFR to convert
	* decimal numbers to fractional dollar numbers, such as securities
	* prices.
	* @param  float   $decimal_dollar is a decimal number.
	* @param  integer $fraction       is the integer to use in the denominator of the fraction.
	* @return float   dollar price expressed as a fraction.
	*/
	function DOLLARFR($decimal_dollar, $fraction) {
		$fraction = intval($fraction);
		$integer = intval($decimal_dollar);
		return ($decimal_dollar - $integer) * $fraction / 100 + $integer;
	}

	/**
	* DDB
	* Returns the depreciation of an asset for a specified period using
	* the double-declining balance method or some other method you specify.
	* @param  float   $cost    is the initial cost of the asset.
	* @param  float   $salvage is the value at the end of the depreciation (sometimes called the salvage value of the asset).
	* @param  integer $life    is the number of periods over which the asset is being depreciated (sometimes called the useful life of the asset).
	* @param  integer $period  is the period for which you want to calculate the depreciation. Period must use the same units as life.
	* @param  float   $factor  is the rate at which the balance declines. If factor is omitted, it is assumed to be 2 (the double-declining balance method).
	* @return float   the depreciation of n periods.
	*/
	function DDB($cost, $salvage, $life, $period, $factor = 2) {
		$x = 0;
		$n = 0;
		$life   = intval($life);
		$period = intval($period);
		while ($period > $n) {
			$x = $factor * $cost / $life;
			if (($cost - $x) < $salvage) $x = $cost- $salvage;
			if ($x < 0) $x = 0;
			$cost -= $x;
			$n++;
		}
		return $x;
	}

	/**
	* SLN
	* Returns the straight-line depreciation of an asset for one period.
	* @param  float   $cost    is the initial cost of the asset.
	* @param  float   $salvage is the value at the end of the depreciation (sometimes called the salvage value of the asset).
	* @param  integer $life    is the number of periods over which the asset is being depreciated (sometimes called the useful life of the asset).
	* @return float   the depreciation allowance for each period.
	*/
	function SLN($cost, $salvage, $life) {
		$sln = ($cost - $salvage) / $life;
		return (is_finite($sln) ? $sln: null);
	}

	/**
	* SYD
	* Returns the sum-of-years' digits depreciation of an asset for
	* a specified period.
	*
	*        (cost - salvage) * (life - per + 1) * 2
	* SYD = -----------------------------------------
	*                  life * (1 + life)
	*
	* @param  float   $cost    is the initial cost of the asset.
	* @param  float   $salvage is the value at the end of the depreciation (sometimes called the salvage value of the asset).
	* @param  integer $life    is the number of periods over which the asset is depreciated (sometimes called the useful life of the asset).
	* @param  integer $per     is the period and must use the same units as life.
	*/
	function SYD($cost, $salvage, $life, $per) {
		$life = intval($life);
		$per  = intval($per);
		$syd  = (($cost - $salvage) * ($life - $per + 1) * 2) / ($life * (1 + $life));
		return (is_finite($syd) ? $syd: null);
	}

	/**
	* @param float $fWert
	* @param float $fRest
	* @param float $fDauer
	* @param float $fPeriode
	* @param float $fFaktor
	* @return float
	*/
	function _ScGetGDA($fWert, $fRest, $fDauer,$fPeriode, $fFaktor) {
		$fZins = $fFaktor / $fDauer;
		if ($fZins >= 1.0) {
			$fZins = 1.0;
			if ($fPeriode == 1.0)
				$fAlterWert = $fWert;
			else
				$fAlterWert = 0.0;
		} else
			$fAlterWert = $fWert * pow(1.0 - $fZins, $fPeriode - 1.0);

		$fNeuerWert = $fWert * pow(1.0 - $fZins, $fPeriode);

		if ($fNeuerWert < $fRest)
			$fGda = $fAlterWert - $fRest;
		else
			$fGda = $fAlterWert - $fNeuerWert;

		if ($fGda < 0.0) $fGda = 0.0;

		return $fGda;
	}

	/**
	* @param  float $cost
	* @param  float $salvage
	* @param  float $life
	* @param  float $life1
	* @param  float $period
	* @param  float $factor
	* @return float
	*/
	function _ScInterVDB($cost, $salvage, $life, $life1, $period, $factor) {
		$fVdb       = 0;
		$fIntEnd    = ceil($period);
		$nLoopEnd   = $fIntEnd;
		$fRestwert  = $cost - $salvage;
		$bNowLia    = false;

		$fLia = 0;
		for ($i = 1; $i <= $nLoopEnd; $i++) {
			if (!$bNowLia) {
				$fGda = $this->_ScGetGDA($cost, $salvage, $life, $i, $factor);
				$fLia = $fRestwert / ($life1 - (float)($i - 1));

				if ($fLia > $fGda) {
					$fTerm   = $fLia;
					$bNowLia = true;
				} else {
					$fTerm      = $fGda;
					$fRestwert -= $fGda;
				}
			} else
				$fTerm = fLia;

			if ($i == $nLoopEnd)
				$fTerm *= ($period + 1.0 - $fIntEnd);
			$fVdb += $fTerm;
		}
		return $fVdb;
	}

	/**
	* VDB
	* Returns the depreciation of an asset for any period you specify,
	* including partial periods, using the double-declining balance method
	* or some other method you specify. VDB stands for variable declining balance.
	*
	* @param  float   $cost         is the initial cost of the asset.
	* @param  float   $salvage      is the value at the end of the depreciation (sometimes called the salvage value of the asset).
	* @param  integer $life         is the number of periods over which the asset is depreciated (sometimes called the useful life of the asset).
	* @param  integer $start_period is the starting period for which you want to calculate the depreciation. Start_period must use the same units as life.
	* @param  integer $end_period   is the ending period for which you want to calculate the depreciation. End_period must use the same units as life.
	* @param  float   $factor       is the rate at which the balance declines. If factor is omitted, it is assumed to be 2 (the double-declining balance method). Change factor if you do not want to use the double-declining balance method.
	* @param  bool    $no_switch    is a logical value specifying whether to switch to straight-line depreciation when depreciation is greater than the declining balance calculation.
	* @return float   the depreciation of an asset.
	*/
	function VDB($cost, $salvage, $life, $start_period, $end_period, $factor = 2.0, $no_switch = false) {
		// pre-validations
		if (($start_period < 0)
			|| ($end_period < $start_period)
			|| ($end_period > $life)
			|| ($cost < 0) || ($salvage > $cost)
			|| ($factor <= 0))
			return null;

		// this implementation is borrowed from OppenOffice 1.0,
		// 'sc/source/core/tool/interpr2.cxx' with a small changes
		// from me.
		$fVdb = 0.0;
		$fIntStart = floor($start_period);
		$fIntEnd = ceil($end_period);

		if ($no_switch) {
			$nLoopStart = (int) $fIntStart;
			$nLoopEnd = (int) $fIntEnd;

			for ($i = $nLoopStart + 1; $i <= $nLoopEnd; $i++) {
				$fTerm = $this->_ScGetGDA($cost, $salvage, $life, $i, $factor);
				if ($i == $nLoopStart + 1)
					$fTerm *= (min($end_period, $fIntStart + 1.0) - $start_period);
				elseif ($i == $nLoopEnd)
					$fTerm *= ($end_period + 1.0 - $fIntEnd);
				$fVdb += $fTerm;
			}
		} else {
			$life1 = $life;

			if ($start_period != $fIntStart)
				if ($factor > 1) {
					if ($start_period >= ($life / 2)) {
						$fPart        = $start_period - ($life / 2);
						$start_period = $life / 2;
						$end_period  -= $fPart;
						$life1       += 1;
					}
				}

			$cost -= $this->_ScInterVDB($cost, $salvage, $life, $life1, $start_period, $factor);
			$fVdb = $this->_ScInterVDB($cost, $salvage, $life, $life - $start_period, $end_period - $start_period, $factor);
		}

		return $fVdb;
	}

	/**
	* Present value interest factor
	*
	*                 nper
	* PVIF = (1 + rate)
	*
	* @param  float   $rate is the interest rate per period.
	* @param  integer $nper is the total number of periods.
	* @return float  the present value interest factor
	*/
	function _calculate_pvif ($rate, $nper) {
		return (pow(1 + $rate, $nper));
	}

	/**
	* Future value interest factor of annuities
	*
	*                   nper
	*          (1 + rate)    - 1
	* FVIFA = -------------------
	*               rate
	*
	* @param  float   $rate is the interest rate per period.
	* @param  integer $nper is the total number of periods.
	* @return float  the present value interest factor of annuities
	*/
	function _calculate_fvifa ($rate, $nper) {
		// Removable singularity at rate == 0
		if ($rate == 0)
			return $nper;
		else
			// FIXME: this sucks for very small rates
			return (pow(1 + $rate, $nper) - 1) / $rate;
	}

	function _calculate_interest_part ($pv, $pmt, $rate, $per) {
		return -($pv * pow(1 + $rate, $per) * $rate +
			$pmt * (pow(1 + $rate, $per) - 1));
	}

	function _calculate_pmt ($rate, $nper, $pv, $fv, $type) {
		// Calculate the PVIF and FVIFA
		$pvif = $this->_calculate_pvif ($rate, $nper);
		$fvifa = $this->_calculate_fvifa ($rate, $nper);

		return ((-$pv * $pvif - $fv ) / ((1.0 + $rate * $type) * $fvifa));
	}

	/**
	* PV
	* Returns the present value of an investment. The present value is
	* the total amount that a series of future payments is worth now.
	* For example, when you borrow money, the loan amount is the present
	* value to the lender.
	*
	* If rate = 0:
	* PV = -(FV + PMT * nper)
	*
	* Else
	*                                 /              nper \
	*                                 | 1 - (1 + rate)    |
	*       PMT * (1 + rate * type) * | ----------------- | - FV
	*                                 \        rate       /
	* PV = ------------------------------------------------------
	*                                nper
	*                       (1 + rate)
	*
	* @param  float   $rate is the interest rate per period.
	* @param  integer $nper is the total number of payment periods in an annuity.
	* @param  float   $pmt  is the payment made each period and cannot change over the life of the annuity.
	* @param  float   $fv   is the future value, or a cash balance you want to attain after the last payment is made.
	* @param  integer $type is the number 0 or 1 and indicates when payments are due.
	* @return float   the present value of an investment.
	*/
	function PV($rate, $nper, $pmt, $fv = 0.0, $type = 0) {
		// Calculate the PVIF and FVIFA
		$pvif  = $this->_calculate_pvif ($rate, $nper);
		$fvifa = $this->_calculate_fvifa ($rate, $nper);

		if ($pvif == 0)
			return null;
		$pv = ((-$fv - $pmt * (1.0 + $rate * $type) * $fvifa) / $pvif);
		return (is_finite($pv) ? $pv: null);
	}

	/**
	 * FV
	 * Returns the future value of an investment based on periodic,
	 * constant payments and a constant interest rate.
	 *
	 * For a more complete description of the arguments in FV, see the PV function.
	 *
	 * Rate: is the interest rate per period.
	 * Nper: is the total number of payment periods in an annuity.
	 * Pmt: is the payment made each period; it cannot change over the life of the
	 *  annuity. Typically, pmt contains principal and interest but no other fees
	 *  or taxes. If pmt is omitted, you must include the pv argument.
	 * Pv: is the present value, or the lump-sum amount that a series of future
	 *  payments is worth right now. If pv is omitted, it is assumed to be 0 (zero),
	 *  and you must include the pmt argument.
	 * Type: is the number 0 or 1 and indicates when payments are due. If type is
	 *  omitted, it is assumed to be 0.
	 *  0 or omitted, At the end of the period
	 *  1, At the beginning of the period
	 *
	 * If rate = 0:
	 * FV = -(PV + PMT * nper)
	 *
	 * Else
	 *                                  /             nper \
	 *                                 | 1 - (1 + rate)     |                 nper
	 * FV =  PMT * (1 + rate * type) * | ------------------ | - PV * (1 + rate)
	 *                                  \        rate      /
	 *
	 **/
	function FV($rate, $nper, $pmt, $pv = 0.0, $type = 0) {
		$pvif = $this->_calculate_pvif ($rate, $nper);
		$fvifa = $this->_calculate_fvifa ($rate, $nper);

		$fv = (-(($pv * $pvif) + $pmt *
				(1.0 + $rate * $type) * $fvifa));

		return (is_finite($fv) ? $fv: null);
	}

	/**
	 * FVSCHEDULE
	 * Returns the future value of an initial principal after applying a series
	 * of compound interest rates. Use FVSCHEDULE to calculate the future value
	 * of an investment with a variable or adjustable rate.
	 *
	 **/
	function FVSCHEDULE($principal, $schedule) {
		$n = count($schedule);
		for ($i = 0; $i < $n; $i++)
			$principal *= 1 + $schedule[$i];
		return $principal;
	}

	/**
	 * PMT
	 * Calculates the payment for a loan based on constant payments
	 * and a constant interest rate.
	 *
	 * For a more complete description of the arguments in PMT, see the PV function.
	 *
	 * Rate: is the interest rate for the loan.
	 * Nper: is the total number of payments for the loan.
	 * Pv: is the present value, or the total amount that a series of future payments
	 *  is worth now; also known as the principal.
	 * Fv: is the future value, or a cash balance you want to attain after the last
	 *  payment is made. If fv is omitted, it is assumed to be 0 (zero), that is,
	 *  the future value of a loan is 0.
	 * Type: is the number 0 (zero) or 1 and indicates when payments are due.
	 *  0 or omitted, At the end of the period
	 *  1, At the beginning of the period
	 *
	 * If rate = 0:
	 *        -(FV + PV)
	 * PMT = ------------
	 *           nper
	 *
	 * Else
	 *
	 *                                      nper
	 *                   FV + PV * (1 + rate)
	 * PMT = --------------------------------------------
	 *                             /             nper \
	 *                            | 1 - (1 + rate)     |
	 *        (1 + rate * type) * | ------------------ |
	 *                             \        rate      /
	 *
	 **/
	function PMT($rate, $nper, $pv, $fv = 0.0, $type = 0) {
		$pmt = $this->_calculate_pmt ($rate, $nper, $pv, $fv, $type);
		return (is_finite($pmt) ? $pmt: null);
	}

	/**
	 * IPMT
	 * Returns the interest payment for a given period for an investment based
	 * on periodic, constant payments and a constant interest rate.
	 *
	 * For a more complete description of the arguments in IPMT, see the PV function.
	 *
	 */
	function IPMT($rate, $per, $nper, $pv, $fv = 0.0, $type = 0) {
		if (($per < 1) || ($per >= ($nper + 1)))
			return null;
		else {
			$pmt = $this->_calculate_pmt ($rate, $nper, $pv, $fv, $type);
			$ipmt = $this->_calculate_interest_part ($pv, $pmt, $rate, $per - 1);
			return (is_finite($ipmt) ? $ipmt: null);
		}
	}

	/**
	 * PPMT
	 * Returns the payment on the principal for a given period for an
	 * investment based on periodic, constant payments and a constant
	 * interest rate.
	 *
	 **/
	function PPMT($rate, $per, $nper, $pv, $fv = 0.0, $type = 0) {
		if (($per < 1) || ($per >= ($nper + 1)))
			return null;
		else {
			$pmt = $this->_calculate_pmt ($rate, $nper, $pv, $fv, $type);
			$ipmt = $this->_calculate_interest_part ($pv, $pmt, $rate, $per - 1);
			return ((is_finite($pmt) && is_finite($ipmt)) ? $pmt - $ipmt: null);
		}
	}

	/**
	 * NPER
	 * Returns the number of periods for an investment based on periodic,
	 * constant payments and a constant interest rate.
	 *
	 * For a complete description of the arguments nper, pmt, pv, fv, and type, see PV.
	 *
	 * Nper: is the total number of payment periods in an annuity.
	 * Pmt: is the payment made each period and cannot change over the life
	 *  of the annuity. Typically, pmt includes principal and interest but no
	 *  other fees or taxes. If pmt is omitted, you must include the fv argument.
	 * Pv: is the present value � the total amount that a series of future payments
	 *  is worth now.
	 * Fv: is the future value, or a cash balance you want to attain after the
	 *  last payment is made. If fv is omitted, it is assumed to be 0 (the future
	 *  value of a loan, for example, is 0).
	 * Type: is the number 0 or 1 and indicates when payments are due.
	 *  0 or omitted, At the end of the period
	 *  1, At the beginning of the period
	 *
	 * If rate = 0:
	 *        -(FV + PV)
	 * nper = -----------
	 *           PMT
	 *
	 * Else
	 *              / PMT * (1 + rate * type) - FV * rate \
	 *         log | ------------------------------------- |
	 *              \ PMT * (1 + rate * type) + PV * rate /
	 * nper = -----------------------------------------------
	 *                          log (1 + rate)
	 *
	 **/
	function NPER($rate, $pmt, $pv, $fv = 0.0, $type = 0) {
		if (($rate == 0) && ($pmt != 0))
			$nper = (-($fv + $pv) / $pmt);
		elseif ($rate <= 0.0)
			return null;
		else {
			$tmp = ($pmt * (1.0 + $rate * $type) - $fv * $rate) /
					($pv * $rate + $pmt * (1.0 + $rate * $type));
			if ($tmp <= 0.0)
				return null;
			$nper = (log10($tmp) / log10(1.0 + $rate));
		}
		return (is_finite($nper) ? $nper: null);
	}

	/*
	 * EFFECT
	 * Returns the effective annual interest rate, given the nominal annual
	 * interest rate and the number of compounding periods per year.
	 *
	 *           /     nominal_rate \ npery
	 * EFFECT = | 1 + -------------- |       - 1
	 *           \         npery    /
	 *
	 **/
	function EFFECT($nominal_rate, $npery) {
		$npery = intval($npery);
		if (($nominal_rate <= 0) || ($npery < 1)) return null;
		$effect = pow(1 + $nominal_rate / $npery, $npery) - 1;
		return (is_finite($effect) ? $effect: null);
	}

	/**
	 * NOMINAL
	 * Returns the nominal annual interest rate, given the effective rate
	 * and the number of compounding periods per year.
	 *
	 *                                   (1 / npery)
	 * NOMINAL = npery * (1 + effect_rate)           -  npery
	 *
	 **/
	function NOMINAL($effect_rate, $npery) {
		$npery = intval($npery);
		if (($effect_rate <= 0) || ($npery < 1)) return null;
		$nominal = $npery * pow(1 + $effect_rate, 1 / $npery) - $npery;
		return (is_finite($nominal) ? $nominal: null);
	}

	/**
	 * DISC
	 * Returns the discount rate for a security.
	 *
	 *             redemption - pr
	 * DISC = ---------------------------
	 *         redemption * yearfraction
	 *
	 **/
	function DISC($settlement, $maturity, $pr, $redemption, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (($pr <= 0) || ($redemption <= 0)) return null;
		if ($settlement >= $maturity) return null;

		switch ($basis) {
			case COM_FINANCIAL_BASIS_MSRB_30_360: // US(NASD) 30/360
				$dsm = $this->Thirty360USyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_ACT: // Actual days/actual days
				$dsm = $this->ActualActualyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_360: // Actual days/360
				$dsm = $this->Actual360yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual days/365
				$dsm = $this->Actual365yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_30E_360: // European 30/360
				$dsm = $this->Thirty360EUyearFraction($settlement, $maturity);
				break;
		}
		$disc = ($redemption - $pr) / ($redemption * $dsm);
		return (is_finite($disc) ? $disc: null);
	}

	/**
	 * RECEIVED
	 * Returns the amount received at maturity for a fully invested security.
	 *
	 *                      investment
	 * RECEIVED = -----------------------------
	 *             1 - discount * yearfraction
	 *
	 **/
	function RECEIVED($settlement, $maturity, $investment, $discount, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (($investment <= 0) || ($discount <= 0)) return null;
		if ($settlement >= $maturity) return null;

		switch ($basis) {
			case COM_FINANCIAL_BASIS_MSRB_30_360: // US(NASD) 30/360
				$dsm = $this->Thirty360USyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_ACT: // Actual/actual
				$dsm = $this->ActualActualyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_360: // Actual/360
				$dsm = $this->Actual360yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual/365
				$dsm = $this->Actual365yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_30E_360: // European 30/360
				$dsm = $this->Thirty360EUyearFraction($settlement, $maturity);
				break;
		}
		$received = $investment / (1 - $discount * $dsm);
		return (is_finite($received) ? $received: null);
	}

	/**
	 * INTRATE
	 * Returns the interest rate for a fully invested security.
	 *
	 *	           redemption - investment
	 * INTRATE = ---------------------------
	 *            investment * yearfraction
	 *
	 **/
	function INTRATE($settlement, $maturity, $investment, $redemption, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (($investment <= 0) || ($redemption <= 0)) return null;
		if ($settlement >= $maturity) return null;

		switch ($basis) {
			case COM_FINANCIAL_BASIS_MSRB_30_360: // US(NASD) 30/360
				$dsm = $this->Thirty360USyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_ACT: // Actual/actual
				$dsm = $this->ActualActualyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual/360
				$dsm = $this->Actual360yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual/365
				$dsm = $this->Actual365yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_30E_360: // European 30/360
				$dsm = $this->Thirty360EUyearFraction($settlement, $maturity);
				break;
		}
		$intrate = ($redemption - $investment) / ($investment * $dsm);
		return (is_finite($intrate) ? $intrate: null);
	}

	/**
	 * NPV
	 * Calculates the net present value of an investment by using a
	 * discount rate and a series of future payments (negative values)
	 * and income (positive values).
	 *
	 *        n   /   values(i)  \
	 * NPV = SUM | -------------- |
	 *       i=1 |            i   |
	 *            \  (1 + rate)  /
	 *
	 **/
	function NPV($rate, $values) {
		if (!is_array($values)) return null;

		$npv = 0.0;
		for ($i = 0; $i < count($values); $i++)
		{
			$npv += $values[$i] / pow(1 + $rate, $i + 1);
		}
		return (is_finite($npv) ? $npv: null);
	}

	/**
	 * XNPV
	 * Returns the net present value for a schedule of cash flows that
	 * is not necessarily periodic. To calculate the net present value
	 * for a series of cash flows that is periodic, use the NPV function.
	 *
	 *        n   /                values(i)               \
	 * NPV = SUM | ---------------------------------------- |
	 *       i=1 |           ((dates(i) - dates(1)) / 365)  |
	 *            \ (1 + rate)                             /
	 *
	 **/
	function XNPV($rate, $values, $dates) {
		if ((!is_array($values)) || (!is_array($dates))) return null;
		if (count($values) != count($dates)) return null;

		$xnpv = 0.0;
		for ($i = 0; $i < count($values); $i++)
		{
			$xnpv += $values[$i] / pow(1 + $rate, $this->DATEDIFF('day', $dates[0], $dates[$i]) / 365);
		}
		return (is_finite($xnpv) ? $xnpv: null);
	}

	/*
	 * IRR
	 * Returns the internal rate of return for a series of cash flows
	 * represented by the numbers in values. These cash flows do not
	 * have to be even, as they would be for an annuity. However, the
	 * cash flows must occur at regular intervals, such as monthly or
	 * annually. The internal rate of return is the interest rate
	 * received for an investment consisting of payments (negative
	 * values) and income (positive values) that occur at regular periods.
	 *
	 */
	function IRR($values, $guess = 0.1) {
		if (!is_array($values)) return null;

		// create an initial bracket, with a root somewhere between bot and top
		$x1 = 0.0;
		$x2 = $guess;
		$f1 = $this->NPV($x1, $values);
		$f2 = $this->NPV($x2, $values);
		for ($i = 0; $i < COM_FINANCIAL_MAX_ITERATIONS; $i++)
		{
			if (($f1 * $f2) < 0.0) break;
			if (abs($f1) < abs($f2)) {
				$f1 = $this->NPV($x1 += 1.6 * ($x1 - $x2), $values);
			} else {
				$f2 = $this->NPV($x2 += 1.6 * ($x2 - $x1), $values);
			}
		}
		if (($f1 * $f2) > 0.0) return null;

		$f = $this->NPV($x1, $values);
		if ($f < 0.0) {
			$rtb = $x1;
			$dx = $x2 - $x1;
		} else {
			$rtb = $x2;
			$dx = $x1 - $x2;
		}

		for ($i = 0;  $i < COM_FINANCIAL_MAX_ITERATIONS; $i++)
		{
			$dx *= 0.5;
			$x_mid = $rtb + $dx;
			$f_mid = $this->NPV($x_mid, $values);
			if ($f_mid <= 0.0) $rtb = $x_mid;
			if ((abs($f_mid) < COM_FINANCIAL_ACCURACY) || (abs($dx) < COM_FINANCIAL_ACCURACY)) return $x_mid;
		}
		return null;
	}

	/*
	 * MIRR
	 * Returns the modified internal rate of return for a series
	 * of periodic cash flows. MIRR considers both the cost of
	 * the investment and the interest received on reinvestment
	 * of cash.
	 *
	 **/
	function MIRR($values, $finance_rate, $reinvert_rate) {
		$n = count($values);
		for ($i = 0, $npv_pos = $npv_neg = 0; $i < $n; $i++) {
			$v = $values[$i];
			if ($v >= 0)
				$npv_pos += $v / pow(1.0 + $reinvert_rate, $i);
			else
				$npv_neg += $v / pow(1.0 + $finance_rate, $i);
		}

		if (($npv_neg == 0) || ($npv_pos == 0) || ($reinvert_rate <= -1))
			return null;

		/*
		* I have my doubts about this formula, but it sort of looks like
		* the one Microsoft claims to use and it produces the results
		* that Excel does.  -- MW.
		*/
		$mirr = pow((-$npv_pos * pow(1 + $reinvert_rate, $n))
				/ ($npv_neg * (1 + $reinvert_rate)), (1.0 / ($n - 1))) - 1.0;
		return (is_finite($mirr) ? $mirr: null);
	}

	/*
	 * XIRR
	 * Returns the internal rate of return for a schedule of cash flows
	 * that is not necessarily periodic. To calculate the internal rate
	 * of return for a series of periodic cash flows, use the IRR function.
	 *
	 * Adapted from routine in Numerical Recipes in C, and translated
	 * from the Bernt A Oedegaard algorithm in C
	 *
	 **/
	function XIRR($values, $dates, $guess = 0.1) {
		if ((!is_array($values)) && (!is_array($dates))) return null;
		if (count($values) != count($dates)) return null;

		// create an initial bracket, with a root somewhere between bot and top
		$x1 = 0.0;
		$x2 = $guess;
		$f1 = $this->XNPV($x1, $values, $dates);
		$f2 = $this->XNPV($x2, $values, $dates);
		for ($i = 0; $i < COM_FINANCIAL_MAX_ITERATIONS; $i++)
		{
			if (($f1 * $f2) < 0.0) break;
			if (abs($f1) < abs($f2)) {
				$f1 = $this->XNPV($x1 += 1.6 * ($x1 - $x2), $values, $dates);
			} else {
				$f2 = $this->XNPV($x2 += 1.6 * ($x2 - $x1), $values, $dates);
			}
		}
		if (($f1 * $f2) > 0.0) return null;

		$f = $this->XNPV($x1, $values, $dates);
		if ($f < 0.0) {
			$rtb = $x1;
			$dx = $x2 - $x1;
		} else {
			$rtb = $x2;
			$dx = $x1 - $x2;
		}

		for ($i = 0;  $i < COM_FINANCIAL_MAX_ITERATIONS; $i++)
		{
			$dx *= 0.5;
			$x_mid = $rtb + $dx;
			$f_mid = $this->XNPV($x_mid, $values, $dates);
			if ($f_mid <= 0.0) $rtb = $x_mid;
			if ((abs($f_mid) < COM_FINANCIAL_ACCURACY) || (abs($dx) < COM_FINANCIAL_ACCURACY)) return $x_mid;
		}
		return null;
	}

	/**
	 * RATE
	 *
	 **/
	function RATE($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1) {
		$rate = $guess;
		$i  = 0;
		$x0 = 0;
		$x1 = $rate;

		if (abs($rate) < COM_FINANCIAL_ACCURACY) {
			$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
		} else {
			$f = exp($nper * log(1 + $rate));
			$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
		}
		$y0 = $pv + $pmt * $nper + $fv;
		$y1 = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;

		// find root by secant method
		while ((abs($y0 - $y1) > COM_FINANCIAL_ACCURACY) && ($i < COM_FINANCIAL_MAX_ITERATIONS))
		{
			$rate = ($y1 * $x0 - $y0 * $x1) / ($y1 - $y0);
			$x0 = $x1;
			$x1 = $rate;

			if (abs($rate) < COM_FINANCIAL_ACCURACY) {
				$y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
			} else {
				$f = exp($nper * log(1 + $rate));
				$y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
			}

			$y0 = $y1;
			$y1 = $y;
			$i++;
		}
		return $rate;
	}

	/**
	 * DELTA
	 * Tests whether two values are equal. Returns 1 if number1 = number2; returns 0 otherwise.
	 * Use this function to filter a set of values. For example, by summing several DELTA functions
	 * you calculate the count of equal pairs. This function is also known as the Kronecker Delta function.
	 */
	function DELTA($number1, $number2 = 0) {
		if (is_nan($number1) || is_nan($number2)) return null;
		if ($number1 == $number2) {
			return 1;
		} else {
			return 0;
		}
	}

	/*
	 * Returns the yield on a security that pays periodic interest.
	 * Use YIELD to calculate bond yield.
	 *
	 * Settlement: is the security's settlement date. The security
	 *  settlement date is the date after the issue date when the
	 *  security is traded to the buyer.
	 * Maturity: is the security's maturity date. The maturity date
	 *  is the date when the security expires.
	 * Rate: is the security's annual coupon rate.
	 * Pr: is the security's price per $100 face value.
	 * Redemption: is the security's redemption value per $100 face value.
	 * Frequency: is the number of coupon payments per year. For annual
	 *  payments, frequency = 1; for semiannual, frequency = 2; for
	 *  quarterly, frequency = 4.
	 * Basis: is the type of day count basis to use.
	 *  0 or omitted US (NASD) 30/360
	 *  1 Actual/actual
	 *  2 Actual/360
	 *  3 Actual/365
	 *  4 European 30/360
	 *
	 */
	function YIELD($settlement, $maturity, $rate, $pr, $redemption, $frequency, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (!$this->_is_valid_frequency($frequency)) return null;
		if ($rate < 0) return null;
		if (($pr <= 0) || ($redemption <= 0)) return null;
		if ($settlement >= $maturity) return null;

		// TODO: Not yet implemented
		return null;
	}

	/**
	 * PRICEDISC
	 * Returns the price per $100 face value of a discounted security.
	 **/
	function PRICEDISC($settlement, $maturity, $discount, $redemption, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (($discount <= 0) || ($redemption <= 0)) return null;
		if ($settlement >= $maturity) return null;

		switch ($basis) {
			case COM_FINANCIAL_BASIS_MSRB_30_360: // US(NASD) 30/360
				$dsm = $this->Thirty360USyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_ACT: // Actual/actual
				$dsm = $this->ActualActualyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_360: // Actual/360
				$dsm = $this->Actual360yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual/365
				$dsm = $this->Actual365yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_30E_360: // European 30/360
				$dsm = $this->Thirty360EUyearFraction($settlement, $maturity);
				break;
		}

		return $redemption - $discount * $redemption * $dsm;
	}

	/**
	 * YIELDDISC
	 * Returns the annual yield for a discounted security.
	 **/
	function YIELDDISC($settlement, $maturity, $pr, $redemption, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (($pr <= 0) || ($redemption <= 0)) return null;
		if ($settlement >= $maturity) return null;

		switch ($basis) {
			case COM_FINANCIAL_BASIS_MSRB_30_360: // US(NASD) 30/360
				$dsm = $this->Thirty360USyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_ACT: // Actual/actual
				$dsm = $this->ActualActualyearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_360: // Actual/360
				$dsm = $this->Actual360yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual/365
				$dsm = $this->Actual365yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_30E_360: // European 30/360
				$dsm = $this->Thirty360EUyearFraction($settlement, $maturity);
				break;
		}

		return ($redemption - $pr) / ($pr * $dsm);
	}

	/**
	 * COUPNUM
	 * Returns the number of coupons payable between the settlement
	 * date and maturity date, rounded up to the nearest whole coupon.
	 *
	 */
	function COUPNUM($settlement, $maturity, $frequency, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (!$this->_is_valid_frequency($frequency)) return null;
		if ($settlement >= $maturity) return null;

		switch ($basis) {
			case COM_FINANCIAL_BASIS_MSRB_30_360: // US(NASD) 30/360
				$dsm = $this->Thirty360USdayCount($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_ACT: // Actual/actual
				$dsm = $this->ActualActualdayCount($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_360: // Actual/360
				$dsm = $this->Actual360dayCount($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual/365
				$dsm = $this->Actual365yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_30E_360: // European 30/360
				$dsm = $this->Thirty360EUdayCount($settlement, $maturity);
				break;
		}

		switch ($frequency) {
			case 1: // anual payments
				return ceil($dsm / 360);
			case 2: // semiannual
				return ceil($dsm / 180);
			case 4: // quarterly
				return ceil($dsm / 90);
		}
		return null;
	}

	/**
	 * COUPDAYBS
	 * Returns the number of days in the coupon period that contains
	 * the settlement date.
	 *
	 */
	function COUPDAYBS($settlement, $maturity, $frequency, $basis = COM_FINANCIAL_BASIS_MSRB_30_360) {
		if (!$this->_is_valid_basis($basis)) return null;
		if (!$this->_is_valid_frequency($frequency)) return null;
		if ($settlement >= $maturity) return null;

		switch ($basis) {
			case COM_FINANCIAL_BASIS_MSRB_30_360: // US(NASD) 30/360
				$dsm = $this->Thirty360USdayCount($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_ACT: // Actual/actual
				$dsm = $this->ActualActualdayCount($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_360: // Actual/360
				$dsm = $this->Actual360dayCount($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_ACT_365: // Actual/365
				$dsm = $this->Actual365yearFraction($settlement, $maturity);
				break;
			case COM_FINANCIAL_BASIS_30E_360: // European 30/360
				$dsm = $this->Thirty360EUdayCount($settlement, $maturity);
				break;
		}

		switch ($frequency) {
			case 1: // anual payments
				return 365 - ($dsm % 360);
			case 2: // semiannual
				return 365 - ($dsm % 360);
			case 4: // quarterly
				return $this->DATEDIFF('day', $this->DATEADD('day', -ceil($dsm / 90) * 90 - ($dsm % 90), $maturity), $settlement);
		}
		return null;
	}
}

?>