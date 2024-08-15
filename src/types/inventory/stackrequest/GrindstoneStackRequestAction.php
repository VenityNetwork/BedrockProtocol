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

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Repair and/or remove enchantments from an item in a grindstone.
 */
final class GrindstoneStackRequestAction extends ItemStackRequestAction{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_GRINDSTONE;

	public function __construct(
		private int $recipeId,
		private int $repairCost,
		private int $repetitions
	){}

	public function getRecipeId() : int{ return $this->recipeId; }

	/** WARNING: This may be negative */
	public function getRepairCost() : int{ return $this->repairCost; }

	public function getRepetitions() : int{ return $this->repetitions; }

	public static function read(PacketSerializer $in) : self{
		$recipeId = $in->readGenericTypeNetworkId();
		$repairCost = $in->getVarInt(); //WHY!!!!
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$repetitions = $in->getByte();
		}

		return new self($recipeId, $repairCost, $repetitions ?? 0);
	}

	public function write(PacketSerializer $out) : void{
		$out->writeGenericTypeNetworkId($this->recipeId);
		$out->putVarInt($this->repairCost);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$out->putByte($this->repetitions);
		}
	}
}
