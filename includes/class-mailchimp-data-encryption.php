<?php
/**
 * Class responsible for encrypting and decrypting data.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Data_Encryption
 *
 * @since 1.6.0
 */
class Mailchimp_Data_Encryption {

	/**
	 * Key to use for encryption.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $key;

	/**
	 * Salt to use for encryption.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $salt;

	/**
	 * Constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {
		$this->key  = $this->get_default_key();
		$this->salt = $this->get_default_salt();
	}

	/**
	 * Encrypts a value.
	 *
	 * If a user-based key is set, that is used. Otherwise the default key is used.
	 *
	 * @since 1.6.0
	 *
	 * @param string $value Value to encrypt.
	 * @return string|bool Encrypted value, or false on failure.
	 */
	public function encrypt( $value ) {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $value;
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $ivlen );

		$raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
		if ( ! $raw_value ) {
			return false;
		}

		return base64_encode( $iv . $raw_value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypts a value.
	 *
	 * If a user-based key is set, that is used. Otherwise the default key is used.
	 *
	 * @since 1.6.0
	 *
	 * @param string $raw_value Value to decrypt.
	 * @return string|bool Decrypted value, or false on failure.
	 */
	public function decrypt( $raw_value ) {
		if ( ! extension_loaded( 'openssl' ) || ! is_string( $raw_value ) ) {
			return $raw_value;
		}

		$decoded_value = base64_decode( $raw_value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( false === $decoded_value ) {
			return $raw_value;
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = substr( $decoded_value, 0, $ivlen );

		$decoded_value = substr( $decoded_value, $ivlen );

		$value = openssl_decrypt( $decoded_value, $method, $this->key, 0, $iv );
		if ( ! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
			return false;
		}

		return substr( $value, 0, - strlen( $this->salt ) );
	}

	/**
	 * Gets the default encryption key to use.
	 *
	 * @since 1.6.0
	 *
	 * @return string Default (not user-based) encryption key.
	 */
	private function get_default_key() {
		if ( defined( 'MAILCHIMP_SF_ENCRYPTION_KEY' ) && '' !== MAILCHIMP_SF_ENCRYPTION_KEY ) {
			return MAILCHIMP_SF_ENCRYPTION_KEY;
		}

		if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
			return LOGGED_IN_KEY;
		}

		// Ideally this default is never used but we have it just in case.
		return 'vJgwa_qf0u(k!uir[rB);g;DciNAKuX;+q&`A+z&m6kX3Y|$q.U3:Q>!$)6CA+=O';
	}

	/**
	 * Gets the default encryption salt to use.
	 *
	 * @since 1.6.0
	 *
	 * @return string Encryption salt.
	 */
	private function get_default_salt() {
		if ( defined( 'MAILCHIMP_SF_ENCRYPTION_SALT' ) && '' !== MAILCHIMP_SF_ENCRYPTION_SALT ) {
			return MAILCHIMP_SF_ENCRYPTION_SALT;
		}

		if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
			return LOGGED_IN_SALT;
		}

		// Ideally this default is never used but we have it just in case.
		return '|qhC}/w6q+$V`H>wM:AtNpg/{s)g<k{F:WMcvJJD[K6c_Kb1OEy^Yx7f|$Ovm+X|';
	}
}
