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

final class CreativeContentEntry{
	public function __construct(
		private int $entryId,
		private ItemStack $item,
		private int $groupId
	){}

	public function getEntryId() : int{ return $this->entryId; }

	public function getItem() : ItemStack{ return $this->item; }

	public function getGroupId() : int{ return $this->groupId; }

	public static function read(PacketSerializer $in) : self{
		$entryId = $in->readGenericTypeNetworkId();
		$item = $in->getItemStackWithoutStackId();
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_776){
			$groupId = $in->getUnsignedVarInt();
		}
		return new self($entryId, $item, $groupId ?? -1);
	}

	public function write(PacketSerializer $out) : void{
		$out->writeGenericTypeNetworkId($this->entryId);
		$out->putItemStackWithoutStackId($this->item);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_776){
			$out->putUnsignedVarInt($this->groupId);
		}
	}
}
