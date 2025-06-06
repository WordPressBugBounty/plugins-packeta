<?php
/**
 * Class BaseResponse.
 *
 * @package Packetery\Core\Api\Soap\Response
 */

declare( strict_types=1 );

namespace Packetery\Core\Api\Soap\Response;

/**
 * Class BaseResponse.
 *
 * @package Packetery\Core\Api\Soap\Response
 */
class BaseResponse {

	/**
	 * Fault identifier.
	 *
	 * @var ?string
	 */
	protected $fault;

	/**
	 * Fault string.
	 *
	 * @var ?string
	 */
	private $faultString;

	/**
	 * Checks if is faulty.
	 *
	 * @return bool
	 */
	public function hasFault(): bool {
		return (bool) $this->fault;
	}

	/**
	 * Checks if password is faulty.
	 *
	 * @return bool
	 */
	public function hasWrongPassword(): bool {
		return ( $this->fault === 'IncorrectApiPasswordFault' );
	}

	/**
	 * Tells if API returns PacketIdsFault fault.
	 *
	 * @return bool
	 */
	public function hasPacketIdsFault(): bool {
		return $this->fault === 'PacketIdsFault';
	}

	/**
	 * Tells if API returns PacketIdFault fault.
	 *
	 * @return bool
	 */
	public function hasPacketIdFault(): bool {
		return $this->fault === 'PacketIdFault';
	}

	/**
	 * Tells if API returns InvalidCourierNumber fault.
	 *
	 * @return bool
	 */
	public function hasInvalidCourierNumberFault(): bool {
		return $this->fault === 'InvalidCourierNumber';
	}

	/**
	 * Sets fault identifier.
	 *
	 * @param string $fault Fault identifier.
	 */
	public function setFault( string $fault ): void {
		$this->fault = $fault;
	}

	/**
	 * Sets fault string.
	 *
	 * @param string $faultString Fault string.
	 */
	public function setFaultString( string $faultString ): void {
		$this->faultString = $faultString;
	}

	/**
	 * Gets fault string.
	 *
	 * @return string|null
	 */
	public function getFaultString(): ?string {
		return $this->faultString;
	}
}
