# php-ldap2json

REST interface which requests data from an LDAP server

## Citrix NetScaler

Example policy usage with a Citrix NetScaler and XPATH_JSON:

```
SYS.HTTP_CALLOUT(callout_json).XPATH_JSON(xp%/mail%)
```
