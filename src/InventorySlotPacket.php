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
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class InventorySlotPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_SLOT_PACKET;

	public int $windowId;
	public int $inventorySlot;
	public ItemStackWrapper $item;
	public int $dynamicContainerId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $windowId, int $inventorySlot, ItemStackWrapper $item, int $dynamicContainerId) : self{
		$result = new self;
		$result->windowId = $windowId;
		$result->inventorySlot = $inventorySlot;
		$result->item = $item;
		$result->dynamicContainerId = $dynamicContainerId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->windowId = $in->getUnsignedVarInt();
		$this->inventorySlot = $in->getUnsignedVarInt();
		$this->item = ItemStackWrapper::read($in);
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$this->dynamicContainerId = $in->getUnsignedVarInt();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt($this->windowId);
		$out->putUnsignedVarInt($this->inventorySlot);
		$this->item->write($out);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$out->putUnsignedVarInt($this->dynamicContainerId);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleInventorySlot($this);
	}
}
