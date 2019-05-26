-- MySQL dump 10.17  Distrib 10.3.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: syncronbytesmgr
-- ------------------------------------------------------
-- Server version	10.3.14-MariaDB-1

/*!40000 ALTER TABLE `user` DISABLE KEYS */;

INSERT INTO `user` VALUES (1,'degabnoti3263@syncronbytes-mgr.jsys','["ROLE_ADMIN"]','$argon2i$v=19$m=1024,t=2,p=2$MDkueXpHZ1BleVJDMW1pTA$/MrGYy8oPa2Hiq1p+lvk0yanDsKpYtfF+0ys6SdZAO0')
ON DUPLICATE KEY UPDATE email = 'degabnoti3263@syncronbytes-mgr.jsys', roles = '["ROLE_ADMIN"]', password = '$argon2i$v=19$m=1024,t=2,p=2$MDkueXpHZ1BleVJDMW1pTA$/MrGYy8oPa2Hiq1p+lvk0yanDsKpYtfF+0ys6SdZAO0';

INSERT INTO `user` VALUES (2,'axguestgrande@syncronbytes-mgr.jsys','["ROLE_ADMIN"]','$argon2i$v=19$m=1024,t=2,p=2$NTBReDZmSS5OMXk0Z1l6OA$M9nTOuenLrZM2Xi+iPS1lifXhcOmeNApaO/8+QmL1DY')
ON DUPLICATE KEY UPDATE email = 'axguestgrande@syncronbytes-mgr.jsys', roles = '["ROLE_ADMIN"]', password = '$argon2i$v=19$m=1024,t=2,p=2$NTBReDZmSS5OMXk0Z1l6OA$M9nTOuenLrZM2Xi+iPS1lifXhcOmeNApaO/8+QmL1DY';

/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Dump completed on 2019-04-24 16:40:27
