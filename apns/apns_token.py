#!/usr/bin/env python

# requires pyjwt (https://pyjwt.readthedocs.io/en/latest/)
# pip install pyjwt


import datetime
import jwt

APNS_AUTH_KEY = './AuthKey_WPYX2ZFPW6.p8'
f = open(APNS_AUTH_KEY)
secret = f.read()

keyId = "WPYX2ZFPW6"
teamId = "AP48SCT3J2"
alg = 'ES256'

time_now = datetime.datetime.now()

headers = {
	"alg": alg,
	"kid": keyId
}

payload = {
	"iss": teamId,
	"iat": int(time_now.strftime("%s"))
}


if __name__ == "__main__":
	"""Create an auth token"""
	token = jwt.encode(payload, secret, algorithm=alg, headers=headers)

	print token