-- MySQL dump 10.16  Distrib 10.1.43-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: resourcespace
-- ------------------------------------------------------
-- Server version	10.1.43-MariaDB-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `resource_type_field`
--

DROP TABLE IF EXISTS `resource_type_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_type_field` (
  `ref` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `title` varchar(400) DEFAULT NULL,
  `field_constraint` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `order_by` int(11) DEFAULT '0',
  `keywords_index` int(11) DEFAULT '0',
  `partial_index` int(11) DEFAULT '0',
  `resource_type` int(11) DEFAULT '0',
  `resource_column` varchar(50) DEFAULT NULL,
  `display_field` int(11) DEFAULT '1',
  `use_for_similar` int(11) DEFAULT '1',
  `iptc_equiv` varchar(20) DEFAULT NULL,
  `display_template` text,
  `tab_name` varchar(50) DEFAULT NULL,
  `required` int(11) DEFAULT '0',
  `smart_theme_name` varchar(200) DEFAULT NULL,
  `exiftool_field` varchar(200) DEFAULT NULL,
  `advanced_search` int(11) DEFAULT '1',
  `simple_search` int(11) DEFAULT '0',
  `help_text` text,
  `display_as_dropdown` int(11) DEFAULT '0',
  `external_user_access` int(11) DEFAULT '1',
  `autocomplete_macro` text,
  `hide_when_uploading` int(11) DEFAULT '0',
  `hide_when_restricted` int(11) DEFAULT '0',
  `value_filter` text,
  `exiftool_filter` text,
  `omit_when_copying` int(11) DEFAULT '0',
  `tooltip_text` text,
  `regexp_filter` varchar(400) DEFAULT NULL,
  `sync_field` int(11) DEFAULT NULL,
  `display_condition` varchar(400) DEFAULT NULL,
  `onchange_macro` text,
  `linked_data_field` text,
  `automatic_nodes_ordering` tinyint(1) DEFAULT '0',
  `fits_field` varchar(255) DEFAULT NULL,
  `personal_data` tinyint(1) DEFAULT '0',
  `include_in_csv_export` tinyint(1) DEFAULT '1',
  `browse_bar` tinyint(1) DEFAULT '1',
  `read_only` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ref`),
  KEY `resource_type` (`resource_type`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_type_field`
--

LOCK TABLES `resource_type_field` WRITE;
/*!40000 ALTER TABLE `resource_type_field` DISABLE KEYS */;
INSERT INTO `resource_type_field` VALUES (9,'extract','Document extract',NULL,1,380,0,0,2,NULL,1,0,NULL,'<div class=\"RecordStory\">\n\n  <h1>[title]</h1>\n\n  <p>[value]</p>\n\n</div>',NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(10,'credit','Credit Line',0,0,350,1,0,0,NULL,1,1,'2#080',NULL,NULL,0,NULL,'Source,Creator,Credit,By-line',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,1,1,1,0),(25,NULL,'Notes',NULL,1,370,0,0,0,NULL,1,0,'2#103','<div class=\"RecordStory\">\n\n  <h1>[title]</h1>\n\n  <p>[value]</p>\n\n</div>',NULL,0,NULL,'JobID',1,0,NULL,0,1,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(51,'originalfilename','Original filename',NULL,0,330,1,0,0,'file_path',0,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(52,'camera','Camera make / model',NULL,0,440,0,0,1,NULL,1,0,NULL,NULL,NULL,0,NULL,'Model',1,0,NULL,0,1,NULL,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(72,'text','Extracted text',NULL,5,340,1,0,0,NULL,0,0,NULL,'<div class=\"item\"><h3>[title]</h3><p>[value]</p></div><div class=\"clearerleft\"> </div>',NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(76,'framerate','Frame Rate',NULL,0,10,1,0,3,NULL,1,1,NULL,NULL,NULL,0,NULL,'framerate',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(77,'videobitrate','Video Bitrate',NULL,0,20,1,0,3,NULL,1,1,NULL,NULL,NULL,0,NULL,'videobitrate',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(78,'aspectratio','Aspect Ratio',NULL,0,30,1,0,3,NULL,1,1,NULL,NULL,NULL,0,NULL,'aspectratio',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(79,'videosize','Video Size',NULL,0,40,0,0,3,NULL,1,1,NULL,NULL,NULL,0,NULL,'imagesize',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(80,'duration','Duration',NULL,0,50,0,0,4,NULL,1,1,NULL,NULL,NULL,0,NULL,'duration',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(81,'channelmode','Channel Mode',NULL,0,60,1,0,4,NULL,1,1,NULL,NULL,NULL,0,NULL,'channelmode',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(82,'samplerate','Sample Rate',NULL,0,70,0,0,4,NULL,1,1,NULL,NULL,NULL,0,NULL,'samplerate',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(83,'audiobitrate','Audio Bitrate',NULL,0,80,1,0,4,NULL,1,1,NULL,NULL,NULL,0,NULL,'audiobitrate',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(84,'publisher','Publisher',1,3,40,0,0,0,NULL,1,0,NULL,NULL,NULL,1,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,1,1,0),(85,'category','Category',0,3,50,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(86,'digitalsourcetype','Digital source type',0,3,110,0,0,1,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(87,'sourceinvnr','Inventory number (of artwork/object)',0,0,60,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(88,'nl-titleartwork','NL - Title (of artwork/object)',0,0,80,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,1,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(89,'description','Description',0,1,90,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(90,'imageviewtype','Image view type',0,2,100,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(91,'creatorofimage','Creator of image',0,9,110,1,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(92,'creatorofartworkobje','Creator of artwork/object',0,0,120,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,1,1,NULL,1,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(93,'datecreatedofimage','Date created of image',0,10,130,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,'createdate',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(94,'datecreatedofartwork','Date created of artwork/object',0,14,140,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,1,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(95,'datedigitalized','Date digitalized',0,10,150,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(96,'keywords','Keywords',0,9,160,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(97,'event','Event',0,3,170,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(98,'imagetoworkrelations','Image to work relationship type',0,3,180,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(99,'imagespectrum','Image spectrum',0,7,190,1,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(100,'personsinvolvedinres','Persons involved in research',0,9,200,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(101,'researchperiod','Research period',0,14,210,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,'Aanduiding van start en einde onderzoek of restauratie.',NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(102,'rightusageterms','Right usage terms',0,0,220,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(103,'copyrightnoticeofart','Copyright notice (of artwork/object)',0,9,230,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(104,'creditline','Credit line',0,0,240,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(105,'clearedforusage','Cleared for usage',0,7,250,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(106,'imagecopyrightdate','Image copyright date',0,10,270,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(107,'recommendedimageforp','Recommended image for publication',0,2,280,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(108,'internalnote','Internal note',0,5,290,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,1,1,1,0),(109,'externalnote','External note',0,5,300,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(111,'pidobject','PID object',0,0,310,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(112,'pidafbeelding','PID afbeelding',NULL,0,320,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(113,'en-titleartwork','EN - Title (of artwork/object)',0,0,70,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,1,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(114,'cfu-expiration','Cleared for usage - expiration date',0,4,260,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(115,'objecttype','Object type',0,3,30,0,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(116,'objectrelationwrap','Object Relation Wrap',0,0,10,1,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(117,'relatedrecords','Related Records',0,1,20,1,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,NULL,1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0),(118,'copyrightnoticeofima','Copyright notice (of image)',0,0,360,1,0,0,NULL,1,1,NULL,NULL,NULL,0,NULL,'CopyrightNotice',1,0,NULL,0,1,NULL,0,0,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,1,1,0);
/*!40000 ALTER TABLE `resource_type_field` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-11-28 10:41:18
