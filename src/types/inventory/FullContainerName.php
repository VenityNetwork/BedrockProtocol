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

namespace pocketmine\network\mcpe\protocol\types\inventory;

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class FullContainerName{
	public function __construct(
		private int $containerId,
		private ?int $dynamicId = null
	){}

	public function getContainerId() : int{ return $this->containerId; }

	public function getDynamicId() : ?int{ return $this->dynamicId; }

	public static function read(PacketSerializer $in) : self{
		$containerId = $in->getByte();
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_729){
			$dynamicId = $in->readOptional($in->getLInt(...));
		}elseif($in->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$dynamicId = $in->getLInt();
		}
		return new self($containerId, $dynamicId ?? null);
	}

	public function write(PacketSerializer $out) : void{
		$out->putByte($this->containerId);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_729){
			$out->writeOptional($this->dynamicId, $out->putLInt(...));
		}elseif($out->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$out->putLInt($this->dynamicId ?? 0);
		}
	}
}