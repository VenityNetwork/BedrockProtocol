<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\camera;

use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class CameraPreset{
	public const AUDIO_LISTENER_TYPE_CAMERA = 0;
	public const AUDIO_LISTENER_TYPE_PLAYER = 1;

	public function __construct(
		private string $name,
		private string $parent,
		private ?float $xPosition,
		private ?float $yPosition,
		private ?float $zPosition,
		private ?float $pitch,
		private ?float $yaw,
		private ?Vector2 $viewOffset,
		private ?float $radius,
		private ?int $audioListenerType,
		private ?bool $playerEffects
	){}

	public function getName() : string{ return $this->name; }

	public function getParent() : string{ return $this->parent; }

	public function getXPosition() : ?float{ return $this->xPosition; }

	public function getYPosition() : ?float{ return $this->yPosition; }

	public function getZPosition() : ?float{ return $this->zPosition; }

	public function getPitch() : ?float{ return $this->pitch; }

	public function getYaw() : ?float{ return $this->yaw; }

	public function getViewOffset() : ?Vector2{ return $this->viewOffset; }

	public function getRadius() : ?float{ return $this->radius; }

	public function getAudioListenerType() : ?int{ return $this->audioListenerType; }

	public function getPlayerEffects() : ?bool{ return $this->playerEffects; }

	public static function read(PacketSerializer $in) : self{
		$name = $in->getString();
		$parent = $in->getString();
		$xPosition = $in->readOptional(fn() => $in->getLFloat());
		$yPosition = $in->readOptional(fn() => $in->getLFloat());
		$zPosition = $in->readOptional(fn() => $in->getLFloat());
		$pitch = $in->readOptional(fn() => $in->getLFloat());
		$yaw = $in->readOptional(fn() => $in->getLFloat());
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$viewOffset = $in->readOptional(fn() => $in->getVector2());
			$radius = $in->readOptional(fn() => $in->getLFloat());
		}
		$audioListenerType = $in->readOptional(fn() => $in->getByte());
		$playerEffects = $in->readOptional(fn() => $in->getBool());

		return new self(
			$name,
			$parent,
			$xPosition,
			$yPosition,
			$zPosition,
			$pitch,
			$yaw,
			$viewOffset ?? null,
			$radius ?? null,
			$audioListenerType,
			$playerEffects
		);
	}

	public function write(PacketSerializer $out) : void{
		$out->putString($this->name);
		$out->putString($this->parent);
		$out->writeOptional($this->xPosition, fn(float $v) => $out->putLFloat($v));
		$out->writeOptional($this->yPosition, fn(float $v) => $out->putLFloat($v));
		$out->writeOptional($this->zPosition, fn(float $v) => $out->putLFloat($v));
		$out->writeOptional($this->pitch, fn(float $v) => $out->putLFloat($v));
		$out->writeOptional($this->yaw, fn(float $v) => $out->putLFloat($v));
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$out->writeOptional($this->viewOffset, fn(Vector2 $v) => $out->putVector2($v));
			$out->writeOptional($this->radius, fn(float $v) => $out->putLFloat($v));
		}
		$out->writeOptional($this->audioListenerType, fn(int $v) => $out->putByte($v));
		$out->writeOptional($this->playerEffects, fn(bool $v) => $out->putBool($v));
	}
}
