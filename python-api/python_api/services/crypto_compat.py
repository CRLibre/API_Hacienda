from __future__ import annotations

import base64
import os

from cryptography.hazmat.primitives import padding
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes


def _normalize_key(passphrase: str) -> bytes:
    raw = passphrase.encode("utf-8")
    if len(raw) >= 32:
        return raw[:32]
    return raw.ljust(32, b"\x00")


def php_compat_encrypt(data: str, passphrase: str) -> str:
    key = _normalize_key(passphrase)
    iv = os.urandom(16)

    padder = padding.PKCS7(128).padder()
    padded = padder.update(data.encode("utf-8")) + padder.finalize()

    cipher = Cipher(algorithms.AES(key), modes.CBC(iv))
    encryptor = cipher.encryptor()
    ciphertext = encryptor.update(padded) + encryptor.finalize()

    # PHP openssl_encrypt (options=0) returns base64 text for ciphertext
    encrypted_data = base64.b64encode(ciphertext).decode("utf-8")
    final = base64.b64encode(encrypted_data.encode("utf-8") + b"::" + iv).decode("utf-8")
    return final


def php_compat_decrypt(data: str, passphrase: str) -> str:
    key = _normalize_key(passphrase)
    decoded = base64.b64decode(data)
    encrypted_data_raw, iv = decoded.split(b"::", 1)
    ciphertext = base64.b64decode(encrypted_data_raw)

    cipher = Cipher(algorithms.AES(key), modes.CBC(iv))
    decryptor = cipher.decryptor()
    padded = decryptor.update(ciphertext) + decryptor.finalize()

    unpadder = padding.PKCS7(128).unpadder()
    plaintext = unpadder.update(padded) + unpadder.finalize()
    return plaintext.decode("utf-8")

