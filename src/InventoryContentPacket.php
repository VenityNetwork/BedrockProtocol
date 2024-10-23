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
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use function count;

class InventoryContentPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_CONTENT_PACKET;

	public int $windowId;
	/** @var ItemStackWrapper[] */
	public array $items = [];
	public FullContainerName $containerName;
	public int $dynamicContainerSize = 0;

	/**
	 * @generate-create-func
	 * @param ItemStackWrapper[] $items
	 */
	public static function create(int $windowId, array $items, FullContainerName $containerName, int $dynamicContainerSize = 0) : self{
		$result = new self;
		$result->windowId = $windowId;
		$result->items = $items;
		$result->containerName = $containerName;
		$result->dynamicContainerSize = $dynamicContainerSize;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->windowId = $in->getUnsignedVarInt();
		$count = $in->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$this->items[] = ItemStackWrapper::read($in);
		}
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_729){
			$this->containerName = FullContainerName::read($in);
			if($in->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
				ItemStackWrapper::read($in); // Storage
				$this->dynamicContainerSize = 0;
			}else{
				$this->dynamicContainerSize = $in->getUnsignedVarInt();
			}
		}elseif($in->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$dynamicId = $in->getUnsignedVarInt();
			$this->containerName = new FullContainerName(0, $dynamicId);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt($this->windowId);
		$out->putUnsignedVarInt(count($this->items));
		foreach($this->items as $item){
			$item->write($out);
		}
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_729){
			$this->containerName->write($out);
			if($out->getProtocol() >= ProtocolInfo::PROTOCOL_748){
				ItemStackWrapper::legacy(ItemStack::null())->write($out); // Storage
			}else {
				$out->putUnsignedVarInt($this->dynamicContainerSize);
			}
		}elseif($out->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$out->putUnsignedVarInt($this->containerName->getDynamicId() ?? 0);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleInventoryContent($this);
	}
}
