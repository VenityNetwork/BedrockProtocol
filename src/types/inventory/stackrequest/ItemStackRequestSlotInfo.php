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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;

final class ItemStackRequestSlotInfo{

	private FullContainerName $containerName;

	public function __construct(
		int $containerId,
		private int $slotId,
		private int $stackId
	){
		$this->containerName = new FullContainerName($containerId);
	}

	public function getContainerId() : int{ return $this->containerName->getContainerId(); }

	public function getContainerName() : FullContainerName{ return $this->containerName; }

	public function getSlotId() : int{ return $this->slotId; }

	public function getStackId() : int{ return $this->stackId; }

	public static function read(PacketSerializer $in) : self{
		$containerName = FullContainerName::read($in);
		$slotId = $in->getByte();
		$stackId = $in->readGenericTypeNetworkId();
		return new self($containerName, $slotId, $stackId);
	}

	public function write(PacketSerializer $out) : void{
		$this->containerName->write($out);
		$out->putByte($this->slotId);
		$out->writeGenericTypeNetworkId($this->stackId);
	}
}
