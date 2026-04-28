<?php
/**
 * Shared date-bucketing helpers used by analytics chart data providers.
 *
 * @package Mailchimp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait Mailchimp_Analytics_Bucketing
 */
trait Mailchimp_Analytics_Bucketing {

	/**
	 * Pick an aggregation interval based on the requested range.
	 *
	 * @param int $days Inclusive day count of the requested range.
	 * @return string One of `daily|weekly|monthly|quarterly|yearly`.
	 */
	public function get_interval( int $days ): string {
		if ( $days <= 30 ) {
			return 'daily';
		}
		if ( $days <= 90 ) {
			return 'weekly';
		}
		if ( $days <= 365 ) {
			return 'monthly';
		}
		if ( $days <= 365 * 3 ) {
			return 'quarterly';
		}
		return 'yearly';
	}

	/**
	 * Build a stable sort key for the bucket a given date falls into.
	 *
	 * @param string            $date     `Y-m-d`.
	 * @param string            $interval Interval name.
	 * @param DateTimeZone|null $tz       Timezone.
	 * @return string
	 */
	public function get_bucket_key( string $date, string $interval, $tz = null ): string {
		$tz = $tz instanceof DateTimeZone ? $tz : wp_timezone();
		$dt = new DateTimeImmutable( $date, $tz );

		switch ( $interval ) {
			case 'weekly':
				// ISO week starts on Monday.
				return $dt->format( 'o-\WW' );
			case 'monthly':
				return $dt->format( 'Y-m' );
			case 'quarterly':
				$quarter = (int) ceil( (int) $dt->format( 'n' ) / 3 );
				return $dt->format( 'Y' ) . '-Q' . $quarter;
			case 'yearly':
				return $dt->format( 'Y' );
			case 'daily':
			default:
				return $dt->format( 'Y-m-d' );
		}
	}

	/**
	 * Build a human-readable label for the bucket a given date falls into.
	 *
	 * @param string            $date     `Y-m-d`.
	 * @param string            $interval Interval name.
	 * @param DateTimeZone|null $tz       Timezone.
	 * @return string
	 */
	public function get_bucket_label( string $date, string $interval, $tz = null ): string {
		$tz = $tz instanceof DateTimeZone ? $tz : wp_timezone();
		$dt = new DateTimeImmutable( $date, $tz );

		switch ( $interval ) {
			case 'weekly':
				$monday = $dt->modify( 'monday this week' );
				if ( $monday > $dt ) {
					$monday = $dt->modify( 'monday last week' );
				}
				return wp_date( 'M j', $monday->getTimestamp(), $tz );
			case 'monthly':
				return wp_date( 'M Y', $dt->getTimestamp(), $tz );
			case 'quarterly':
				$quarter = (int) ceil( (int) $dt->format( 'n' ) / 3 );
				return 'Q' . $quarter . ' ' . $dt->format( 'Y' );
			case 'yearly':
				return $dt->format( 'Y' );
			case 'daily':
			default:
				return wp_date( 'M j', $dt->getTimestamp(), $tz );
		}
	}

	/**
	 * Validate a `Y-m-d` date string.
	 *
	 * @param string $date Candidate date string.
	 * @return bool
	 */
	protected function is_valid_date( string $date ): bool {
		if ( '' === $date ) {
			return false;
		}
		$dt = DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		return $dt && $dt->format( 'Y-m-d' ) === $date;
	}
}
