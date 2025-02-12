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
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeGroupEntry;
use function count;

class CreativeContentPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CREATIVE_CONTENT_PACKET;

	/** @var CreativeGroupEntry[] */
	private array $groups;
	/** @var CreativeContentEntry[] */
	private array $entries;

	/**
	 * @generate-create-func
	 * @param CreativeContentEntry[] $entries
	 */
	public static function create(array $groups, array $entries) : self{
		$result = new self;
		$result->groups = $groups;
		$result->entries = $entries;
		return $result;
	}

	/** @return CreativeGroupEntry[] */
	public function getGroups() : array{ return $this->groups; }

	/** @return CreativeContentEntry[] */
	public function getEntries() : array{ return $this->entries; }

	protected function decodePayload(PacketSerializer $in) : void{
		$this->groups = [];
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
			for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
				$this->groups[] = CreativeGroupEntry::read($in);
			}
		}
		$this->entries = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$this->entries[] = CreativeContentEntry::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
			$out->putUnsignedVarInt(count($this->groups));
			foreach($this->groups as $group){
				$group->write($out);
			}
		}
		$out->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			$entry->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleCreativeContent($this);
	}
}
