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

use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\serializer\BitSet;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\network\mcpe\protocol\types\InteractionMode;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use pocketmine\network\mcpe\protocol\types\ItemInteractionData;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayerBlockAction;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionStopBreak;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\network\mcpe\protocol\types\PlayMode;
use function assert;
use function count;

class PlayerAuthInputPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_AUTH_INPUT_PACKET;

	private Vector3 $position;
	private float $pitch;
	private float $yaw;
	private float $headYaw;
	private float $moveVecX;
	private float $moveVecZ;
	private BitSet $inputFlags;
	private int $inputMode;
	private int $playMode;
	private int $interactionMode;
	private Vector2 $interactRotation;
	private int $tick;
	private Vector3 $delta;
	private ?ItemInteractionData $itemInteractionData = null;
	private ?ItemStackRequest $itemStackRequest = null;
	/** @var PlayerBlockAction[]|null */
	private ?array $blockActions = null;
	private ?PlayerAuthInputVehicleInfo $vehicleInfo = null;
	private float $analogMoveVecX;
	private float $analogMoveVecZ;
	private Vector3 $cameraOrientation;
	private Vector2 $rawMove;

	/**
	 * @param int                      $inputFlags @see PlayerAuthInputFlags
	 * @param int                      $inputMode @see InputMode
	 * @param int                      $playMode @see PlayMode
	 * @param int                      $interactionMode @see InteractionMode
	 * @param Vector3|null             $vrGazeDirection only used when PlayMode::VR
	 * @param PlayerBlockAction[]|null $blockActions Blocks that the client has interacted with
	 */
	public static function create(
		Vector3 $position,
		float $pitch,
		float $yaw,
		float $headYaw,
		float $moveVecX,
		float $moveVecZ,
		BitSet $inputFlags,
		int $inputMode,
		int $playMode,
		int $interactionMode,
		Vector2 $interactRotation,
		int $tick,
		Vector3 $delta,
		?ItemInteractionData $itemInteractionData,
		?ItemStackRequest $itemStackRequest,
		?array $blockActions,
		?PlayerAuthInputVehicleInfo $vehicleInfo,
		float $analogMoveVecX,
		float $analogMoveVecZ,
		Vector3 $cameraOrientation,
		Vector2 $rawMove
	) : self{
		$result = new self;
		$result->position = $position->asVector3();
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->moveVecX = $moveVecX;
		$result->moveVecZ = $moveVecZ;

		if($inputFlags->getLength() !== 65){
			throw new \InvalidArgumentException("Input flags must be 65 bits long");
		}
		$inputFlags->set(PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST, $itemStackRequest !== null);
		$inputFlags->set(PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION, $itemInteractionData !== null);
		$inputFlags->set(PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS, $blockActions !== null);
		$inputFlags->set(PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE, $vehicleInfo !== null);

		$result->inputMode = $inputMode;
		$result->playMode = $playMode;
		$result->interactionMode = $interactionMode;
		$result->interactRotation = $interactRotation;
		$result->tick = $tick;
		$result->delta = $delta;
		$result->itemInteractionData = $itemInteractionData;
		$result->itemStackRequest = $itemStackRequest;
		$result->blockActions = $blockActions;
		$result->vehicleInfo = $vehicleInfo;
		$result->analogMoveVecX = $analogMoveVecX;
		$result->analogMoveVecZ = $analogMoveVecZ;
		$result->cameraOrientation = $cameraOrientation;
		$result->rawMove = $rawMove;
		return $result;
	}

	public function getPosition() : Vector3{
		return $this->position;
	}

	public function getPitch() : float{
		return $this->pitch;
	}

	public function getYaw() : float{
		return $this->yaw;
	}

	public function getHeadYaw() : float{
		return $this->headYaw;
	}

	public function getMoveVecX() : float{
		return $this->moveVecX;
	}

	public function getMoveVecZ() : float{
		return $this->moveVecZ;
	}

	/**
	 * @see PlayerAuthInputFlags
	 */
	public function getInputFlags() : BitSet{
		return $this->inputFlags;
	}

	/**
	 * @see InputMode
	 */
	public function getInputMode() : int{
		return $this->inputMode;
	}

	/**
	 * @see PlayMode
	 */
	public function getPlayMode() : int{
		return $this->playMode;
	}

	/**
	 * @see InteractionMode
	 */
	public function getInteractionMode() : int{
		return $this->interactionMode;
	}

	public function getInteractRotation(): Vector2 {
		return $this->interactRotation;
	}

	public function getTick() : int{
		return $this->tick;
	}

	public function getDelta() : Vector3{
		return $this->delta;
	}

	public function getItemInteractionData() : ?ItemInteractionData{
		return $this->itemInteractionData;
	}

	public function getItemStackRequest() : ?ItemStackRequest{
		return $this->itemStackRequest;
	}

	/**
	 * @return PlayerBlockAction[]|null
	 */
	public function getBlockActions() : ?array{
		return $this->blockActions;
	}

	public function getVehicleInfo(): ?PlayerAuthInputVehicleInfo{
		return $this->vehicleInfo;
	}

	public function getAnalogMoveVecX() : float{ return $this->analogMoveVecX; }

	public function getAnalogMoveVecZ() : float{ return $this->analogMoveVecZ; }

	public function getCameraOrientation(): Vector3 {
		return $this->cameraOrientation;
	}

	public function hasFlag(int $flag) : bool{
		return $this->inputFlags->get($flag);
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->pitch = $in->getLFloat();
		$this->yaw = $in->getLFloat();
		$this->position = $in->getVector3();
		$this->moveVecX = $in->getLFloat();
		$this->moveVecZ = $in->getLFloat();
		$this->headYaw = $in->getLFloat();
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_766){
			$this->inputFlags = BitSet::read($in, 65);
		}else{
			$this->inputFlags = BitSet::readFromUnsignedVarLong($in, 65);
		}
		$this->inputMode = $in->getUnsignedVarInt();
		$this->playMode = $in->getUnsignedVarInt();
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_527){
			$this->interactionMode = $in->getUnsignedVarInt();
		}
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
			$this->interactRotation = $in->getVector2();
		}else{
			$this->interactRotation = new Vector2(0, 0);
			if($this->playMode === PlayMode::VR) {
				$in->getVector3(); // VRGazeDirection
			}
		}
		$this->tick = $in->getUnsignedVarLong();
		$this->delta = $in->getVector3();
		if($this->hasFlag(PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION)){
			$this->itemInteractionData = ItemInteractionData::read($in);
		}
		if($this->hasFlag(PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST)){
			$this->itemStackRequest = ItemStackRequest::read($in);
		}
		if($this->hasFlag(PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS)){
			$this->blockActions = [];
			$max = $in->getVarInt();
			for($i = 0; $i < $max; ++$i){
				$actionType = $in->getVarInt();
				$this->blockActions[] = match(true){
					PlayerBlockActionWithBlockInfo::isValidActionType($actionType) => PlayerBlockActionWithBlockInfo::read($in, $actionType),
					$actionType === PlayerAction::STOP_BREAK => new PlayerBlockActionStopBreak(),
					default => throw new PacketDecodeException("Unexpected block action type $actionType")
				};
			}
		}
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_649){
			if($this->hasFlag(PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE)){
				$this->vehicleInfo = PlayerAuthInputVehicleInfo::read($in);
			}
		}
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_575){
			$this->analogMoveVecX = $in->getLFloat();
			$this->analogMoveVecZ = $in->getLFloat();
		}
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_748){
			$this->cameraOrientation = $in->getVector3();
			if($in->getProtocol() >= ProtocolInfo::PROTOCOL_766){
				$this->rawMove = $in->getVector2();
			}
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);
		$out->putVector3($this->position);
		$out->putLFloat($this->moveVecX);
		$out->putLFloat($this->moveVecZ);
		$out->putLFloat($this->headYaw);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_766){
			$this->inputFlags->write($out);
		}else{
			$this->inputFlags->writeAsUnsignedVarLong($out);
		}
		$out->putUnsignedVarInt($this->inputMode);
		$out->putUnsignedVarInt($this->playMode);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_527){
			$out->putUnsignedVarInt($this->interactionMode);
		}
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
			$out->putVector2($this->interactRotation);
		}else{
			if($this->playMode === PlayMode::VR) {
				$out->putVector3(Vector3::zero());
			}
		}
		$out->putUnsignedVarLong($this->tick);
		$out->putVector3($this->delta);
		if($this->itemInteractionData !== null){
			$this->itemInteractionData->write($out);
		}
		if($this->itemStackRequest !== null){
			$this->itemStackRequest->write($out);
		}
		if($this->blockActions !== null){
			$out->putVarInt(count($this->blockActions));
			foreach($this->blockActions as $blockAction){
				$out->putVarInt($blockAction->getActionType());
				$blockAction->write($out);
			}
		}
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_649){
			if($this->hasFlag(PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE)){
				$this->vehicleInfo->write($out);
			}
		}
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_575){
			$out->putLFloat($this->analogMoveVecX);
			$out->putLFloat($this->analogMoveVecZ);
		}
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_748){
			$out->putVector3($this->cameraOrientation);
			if($out->getProtocol() >= ProtocolInfo::PROTOCOL_766){
				$out->putVector2($this->rawMove);
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handlePlayerAuthInput($this);
	}
}
