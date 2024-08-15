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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraTargetInstruction;

class CameraInstructionPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CAMERA_INSTRUCTION_PACKET;

	private ?CameraSetInstruction $set;
	private ?bool $clear;
	private ?CameraFadeInstruction $fade;
	private ?CameraTargetInstruction $target;
	private ?bool $removeTarget;

	/**
	 * @generate-create-func
	 */
	public static function create(?CameraSetInstruction $set, ?bool $clear, ?CameraFadeInstruction $fade, ?CameraTargetInstruction $target = null, ?bool $removeTarget = null) : self{
		$result = new self;
		$result->set = $set;
		$result->clear = $clear;
		$result->fade = $fade;
		$result->target = $target;
		$result->removeTarget = $removeTarget;
		return $result;
	}

	public function getSet() : ?CameraSetInstruction{ return $this->set; }

	public function getClear() : ?bool{ return $this->clear; }

	public function getFade() : ?CameraFadeInstruction{ return $this->fade; }

	protected function decodePayload(PacketSerializer $in) : void{
		$this->set = $in->readOptional(fn() => CameraSetInstruction::read($in));
		$this->clear = $in->readOptional(fn() => $in->getBool());
		$this->fade = $in->readOptional(fn() => CameraFadeInstruction::read($in));
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$this->target = $in->readOptional(fn() => CameraTargetInstruction::read($in));
			$this->removeTarget = $in->readOptional($in->getBool(...));
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->writeOptional($this->set, fn(CameraSetInstruction $v) => $v->write($out));
		$out->writeOptional($this->clear, fn(bool $v) => $out->putBool($v));
		$out->writeOptional($this->fade, fn(CameraFadeInstruction $v) => $v->write($out));
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$out->writeOptional($this->target, fn(CameraTargetInstruction $v) => $v->write($out));
			$out->writeOptional($this->removeTarget, $out->putBool(...));
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleCameraInstruction($this);
	}
}
