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

class EmotePacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::EMOTE_PACKET;

	public const FLAG_SERVER = 1 << 0;
	public const FLAG_MUTE_ANNOUNCEMENT = 1 << 1;

	private int $actorRuntimeId;
	private string $emoteId;
	private int $emoteLengthTicks = 0;
	private string $xboxUserId;
	private string $platformChatId;
	private int $flags;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, string $emoteId, string $xboxUserId, string $platformChatId, int $flags, int $emoteLengthTicks = 0) : self{
		$result = new self;
		$result->actorRuntimeId = $actorRuntimeId;
		$result->emoteId = $emoteId;
		$result->emoteLengthTicks = $emoteLengthTicks;
		$result->xboxUserId = $xboxUserId;
		$result->platformChatId = $platformChatId;
		$result->flags = $flags;
		return $result;
	}

	public function getActorRuntimeId() : int{
		return $this->actorRuntimeId;
	}

	public function getEmoteId() : string{
		return $this->emoteId;
	}

	public function getEmoteLengthTicks(): int {
		return $this->emoteLengthTicks;
	}

	public function getXboxUserId() : string{ return $this->xboxUserId; }

	public function getPlatformChatId() : string{ return $this->platformChatId; }

	public function getFlags() : int{
		return $this->flags;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->emoteId = $in->getString();
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_729){
			$this->emoteLengthTicks = $in->getUnsignedVarInt();
		}
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_589){
			$this->xboxUserId = $in->getString();
			$this->platformChatId = $in->getString();
		}else{
			$this->xboxUserId = "";
			$this->platformChatId = "";
		}
		$this->flags = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putString($this->emoteId);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_729){
			$out->putUnsignedVarInt($this->emoteLengthTicks);
		}
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_589){
			$out->putString($this->xboxUserId);
			$out->putString($this->platformChatId);
		}
		$out->putByte($this->flags);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleEmote($this);
	}
}
