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

namespace pocketmine\network\mcpe\protocol\types\resourcepacks;

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class ResourcePackInfoEntry{
	public function __construct(
		private string $packId,
		private string $version,
		private int $sizeBytes,
		private string $encryptionKey = "",
		private string $subPackName = "",
		private string $contentId = "",
		private bool $hasScripts = false,
		private bool $isAddonPack = false,
		private bool $isRtxCapable = false,
		private string $cdnUrl = ""
	){}

	public function getPackId() : string{
		return $this->packId;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getSizeBytes() : int{
		return $this->sizeBytes;
	}

	public function getEncryptionKey() : string{
		return $this->encryptionKey;
	}

	public function getSubPackName() : string{
		return $this->subPackName;
	}

	public function getContentId() : string{
		return $this->contentId;
	}

	public function hasScripts() : bool{
		return $this->hasScripts;
	}

	public function isAddonPack(): bool{
		return $this->isAddonPack;
	}

	public function isRtxCapable() : bool{ return $this->isRtxCapable; }

	public function getCdnUrl(): string {
		return $this->cdnUrl;
	}

	public function write(PacketSerializer $out) : void{
		$out->putString($this->packId);
		$out->putString($this->version);
		$out->putLLong($this->sizeBytes);
		$out->putString($this->encryptionKey);
		$out->putString($this->subPackName);
		$out->putString($this->contentId);
		$out->putBool($this->hasScripts);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$out->putBool($this->isAddonPack);
		}
		$out->putBool($this->isRtxCapable);
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
			$out->putString($this->cdnUrl);
		}
	}

	public static function read(PacketSerializer $in) : self{
		$uuid = $in->getString();
		$version = $in->getString();
		$sizeBytes = $in->getLLong();
		$encryptionKey = $in->getString();
		$subPackName = $in->getString();
		$contentId = $in->getString();
		$hasScripts = $in->getBool();
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_712){
			$isAddonPack = $in->getBool();
		}
		$rtxCapable = $in->getBool();
		$cdnUrl = $in->getProtocol() >= ProtocolInfo::PROTOCOL_748 ? $in->getString() : "";
		return new self($uuid, $version, $sizeBytes, $encryptionKey, $subPackName, $contentId, $hasScripts, $isAddonPack ?? false, $rtxCapable, $cdnUrl);
	}
}
