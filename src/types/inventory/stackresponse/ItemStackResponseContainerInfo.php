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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackresponse;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;
use function count;

final class ItemStackResponseContainerInfo{

	private FullContainerName $containerName;

	/**
	 * @param ItemStackResponseSlotInfo[] $slots
	 */
	public function __construct(
		int $containerId,
		private array $slots
	){
		$this->containerName = new FullContainerName($containerId);
	}

	public function getContainerId() : int{ return $this->containerName->getContainerId(); }

	public function getContainerName() : FullContainerName{ return $this->containerName; }

	/** @return ItemStackResponseSlotInfo[] */
	public function getSlots() : array{ return $this->slots; }

	public static function read(PacketSerializer $in) : self{
		$containerName = FullContainerName::read($in);
		$slots = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$slots[] = ItemStackResponseSlotInfo::read($in);
		}
		return new self($containerName->getContainerId(), $slots);
	}

	public function write(PacketSerializer $out) : void{
		$this->containerName->write($out);
		$out->putUnsignedVarInt(count($this->slots));
		foreach($this->slots as $slot){
			$slot->write($out);
		}
	}
}
