<?php

/**
 * Ed25519 signed updates extension server API interface.
 *
 * @package WordPoints
 * @since   2.5.0
 */

/**
 * Interface for a remote API offering extension updates that are Ed25519 signed.
 *
 * @since 2.5.0
 */
interface WordPoints_Extension_Server_API_Updates_Signed_Ed25519I
	extends WordPoints_Extension_Server_API_UpdatesI {

	/**
	 * Gets the Ed25519 public key to check the packages for an extension against.
	 *
	 * @since 2.5.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return string The public key to verify packages with.
	 */
	public function get_extension_public_key_ed25519(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	);

	/**
	 * Gets the signature of the zip package for the latest version of an extension.
	 *
	 * @since 2.5.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return string The package's cryptographic signature.
	 */
	public function get_extension_package_signature_ed25519(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	);
}

// EOF
