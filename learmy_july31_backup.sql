-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: learmy
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs`
--

LOCK TABLES `blogs` WRITE;
/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
INSERT INTO `blogs` VALUES (1,'The Importance of Music in Child Development','the-importance-of-music-in-child-development','Studies show that learning a musical instrument can boost brain power and emotional intelligence in children...','Admin','media/blogs/SoywanGf3CaJJLlxdUUtjhcNf5hgjFV4BU3keSfQ.jpg',NULL,NULL,NULL,'2026-03-17 12:59:01','2026-04-19 02:14:32'),(2,'Preparing for Board Exams: Tips & Tricks','preparing-board-exams-tips','Board exams can be stressful. Here are some strategies to manage your time and study effectively...','Dr. Arnab Kumar',NULL,NULL,NULL,NULL,'2026-03-17 12:59:01','2026-03-17 12:59:01');
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('learmyeducoach-cache-dmin@learmy.com|::1','i:1;',1773774385),('learmyeducoach-cache-dmin@learmy.com|::1:timer','i:1773774385;',1773774385);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Music Learning','music-learning',0,'2026-04-16 12:04:46','2026-04-16 12:12:13'),(2,'Academic Coaching','academic',0,'2026-04-16 12:04:46','2026-04-16 12:04:46'),(3,'Other Programs','other',0,'2026-04-16 12:04:46','2026-04-16 12:04:46');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `features` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `indian_online_fee` decimal(10,2) DEFAULT NULL,
  `indian_offline_fee` decimal(10,2) DEFAULT NULL,
  `intl_online_fee` decimal(10,2) DEFAULT NULL,
  `intl_offline_fee` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_slug_unique` (`slug`),
  KEY `courses_category_id_foreign` (`category_id`),
  CONSTRAINT `courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (6,'Piano /Keyboard','piano-keyboard',1,1,'Our Piano and Keyboard classes are designed for students of all ages and skill levels. Whether you are a beginner learning your first notes or an advanced player refining your technique, our structured lessons cover music theory, hand coordination, rhythm, and performance skills. With personalized guidance and a supportive learning environment, students gain confidence and develop a strong musical foundation.','🎹 Suitable for beginners to advanced learners\r\n👨‍🏫 Experienced and trained instructors\r\n📚 Step-by-step structured curriculum\r\n🎼 Focus on both practical playing and music theory\r\n🎧 Learn to play songs across different genres\r\n✋ Improve hand coordination and finger techniques\r\n⏱️ Flexible class timings\r\n👥 Individual attention for every student\r\n🎤 Performance opportunities and practice sessions\r\n🏠 Online and offline classes available',3500.00,NULL,NULL,NULL,NULL,NULL,NULL,'media/courses/fP3FEQF69IIcADL337teGTNCKhcc2b4bSTeO7wIf.jpg',0,NULL,NULL,NULL,'2026-04-16 11:18:14','2026-04-16 12:27:45'),(7,'Guitar','guitar',1,2,'Our Guitar classes are designed for students of all skill levels, from complete beginners to advanced players. The course covers essential techniques such as chords, strumming patterns, fingerstyle, and music theory. With structured lessons and personalized guidance, students develop confidence, creativity, and the ability to play a wide range of songs.','🎸 Suitable for beginners to advanced learners\r\n👨‍🏫 Experienced and skilled instructors\r\n📚 Structured step-by-step learning program\r\n🎼 Covers chords, strumming, and fingerstyle techniques\r\n🎧 Learn to play popular songs across genres\r\n🎵 Strong focus on rhythm and timing\r\n✋ Improves finger strength and coordination\r\n👥 Personalized attention for each student\r\n⏱️ Flexible class schedules\r\n🎤 Performance and practice sessions\r\n🏠 Online and offline classes available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'2026-04-16 11:22:38','2026-04-16 12:31:00'),(8,'Drums','drums',1,5,'Drums classes are designed for students of all skill levels, from beginners to advanced learners. The course focuses on rhythm, timing, coordination, and essential drumming techniques. Students learn basic beats, fills, stick control, and music theory, helping them build a strong foundation and play confidently across different music styles.','🥁 Suitable for beginners to advanced learners\r\n👨‍🏫 Experienced and professional instructors\r\n📚 Structured and step-by-step learning program\r\n🎼 Focus on rhythm, timing, and coordination\r\n🎵 Learn basic beats, fills, and advanced patterns\r\n✋ Develop stick control and hand-foot coordination\r\n🎧 Play along with songs from different genres\r\n👥 Personalized attention for each student\r\n⏱️ Flexible class timings\r\n🎤 Practice sessions and performance opportunities\r\n🏠 Online and offline classes available',3500.00,NULL,NULL,NULL,NULL,NULL,NULL,'media/courses/F8D4bK5udgjcvzOogw037yN7c1eVwh9JqGnsAVMl.jpg',0,NULL,NULL,NULL,'2026-04-16 11:24:29','2026-04-16 12:33:04'),(9,'Vocal classes','vocal-classes',1,3,'Our Vocal classes offer comprehensive training in Indian, Western, and Carnatic music styles, designed for students of all levels. The program focuses on voice training, pitch control, breathing techniques, and performance skills. With a structured approach and personalized guidance, students develop strong vocal foundations and the ability to sing with confidence and expression across multiple genres.','🎤 Training in Indian, Western, and Carnatic vocals\r\n👨‍🏫 Experienced and qualified vocal instructors\r\n📚 Structured learning for beginners to advanced levels\r\n🎼 Focus on pitch, tone, and voice control\r\n🌬️ Breathing techniques and vocal exercises\r\n🎵 Learn classical and contemporary songs\r\n🎧 Ear training and rhythm development\r\n🗣️ Improves pronunciation and expression\r\n👥 Personalized attention for each student\r\n⏱️ Flexible class timings\r\n🎤 Performance opportunities and stage practice\r\n🏠 Online and offline classes available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'media/courses/XxgSkuDoanTVwasksY5S7JHM1Hq6lR0EEH7d2xw6.jpg',0,NULL,NULL,NULL,'2026-04-16 11:27:58','2026-04-16 12:31:09'),(10,'Chess','chess',3,5,'Our Chess classes are designed to develop strategic thinking, concentration, and problem-solving skills in students of all ages. From basic rules and piece movements to advanced tactics and game strategies, we provide structured training for beginners as well as competitive players. With personalized guidance and practice sessions, students learn to think ahead, analyze positions, and improve their overall game performance.','♟️ Suitable for all skill levels\r\n🧠 Improves focus and logical thinking\r\n🎯 Learn strategy and tactical play\r\n♞ Covers openings, middlegame & endgame\r\n🧩 Practice puzzles and exercises\r\n👨‍🏫 Expert coaching and guidance\r\n👥 Interactive practice games\r\n📊 Game analysis and feedback\r\n🏆 Tournament preparation\r\n🏠 Online & offline classes available',NULL,10000.00,20.00,40.00,50.00,NULL,NULL,'media/courses/NhcERQUp2M9fWCbrENICdpsaUEkyyV0ujUX1cJQU.jpg',1,NULL,NULL,NULL,'2026-04-16 11:49:14','2026-04-19 02:24:17'),(11,'Yoga and meditation','yoga-and-meditation',3,6,'Our Yoga classes are designed to promote physical strength, mental clarity, and overall well-being. We offer a balanced practice that includes asanas, breathing techniques (pranayama), and meditation. Suitable for all age groups, our sessions help improve flexibility, reduce stress, and build a healthier lifestyle under the guidance of experienced instructors.','🧘 Suitable for all ages and levels\r\n🌬️ Focus on breathing and relaxation\r\n🧎 Improves flexibility and strength\r\n🧠 Reduces stress and improves focus\r\n💪 Enhances overall fitness and health\r\n🧘‍♀️ Guided meditation and posture training\r\n👨‍🏫 Experienced instructors\r\n⏱️ Flexible timings\r\n🏠 Online & offline classes available',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'media/courses/4o3MjqRAqvyEvi3UlAokk0mJMI0fsTM4lcCqBnzV.jpg',0,NULL,NULL,NULL,'2026-04-16 11:52:24','2026-04-19 02:24:30'),(12,'Grade 1-12','grade-1-12',2,0,'Our Coaching classes for Grade 1 to 12 are designed to build strong academic foundations and ensure consistent academic growth. We focus on clear concept understanding, regular practice, and personalized attention for each student. With structured lessons across all subjects, we help students improve performance, boost confidence, and excel in school exams.','📚 Coaching for Grade 1 to 12 (All Subjects)\r\n👨‍🏫 Experienced and qualified teachers\r\n🧠 Strong focus on concept clarity\r\n📝 Regular tests and performance evaluation\r\n📖 Homework support and doubt solving\r\n🎯 Exam-oriented preparation and revision\r\n👥 Individual attention for every student\r\n📊 Progress tracking and feedback system\r\n🕒 Flexible class timings\r\n🏠 Online & offline classes available\r\n🏆 Focus on academic improvement and results',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'media/courses/akSBuzYjae1rZCuA4jLSytO20uVlArEUdHbkwklN.jpg',0,NULL,NULL,NULL,'2026-04-16 12:18:45','2026-04-16 12:18:45'),(13,'Art and Craft','art-and-craft',3,7,'Join our Art and Craft Class and unlock your creativity in a fun, inspiring environment! This class is designed for learners of all ages and skill levels, where imagination meets hands-on learning. Students will explore a variety of artistic techniques, including drawing, painting, paper crafts, DIY projects, and more.\r\n\r\nOur sessions encourage self-expression, improve fine motor skills, and build confidence through creative exploration. With step-by-step guidance and plenty of room for personal ideas, every participant gets the chance to create unique and beautiful artworks.','• Suitable for all age groups and skill levels\r\n• Hands-on learning with a variety of art and craft techniques\r\n• Activities include drawing, painting, paper crafts, and DIY projects\r\n• Encourages creativity and self-expression\r\n• Step-by-step guidance from experienced instructors\r\n• Helps develop fine motor skills and concentration\r\n• Fun, engaging, and interactive sessions\r\n• Use of safe and quality art materials\r\n• Opportunity to create unique, take-home artworks\r\n• Builds confidence and imagination in a supportive environment\r\n• Regular new themes and creative projects',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'media/courses/IiyL5QlKJtqn45dpGfsJS1BjVgeCg7u79cHQfLQN.jpg',1,NULL,NULL,NULL,'2026-04-19 02:22:24','2026-04-19 02:23:46');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enquiries`
--

DROP TABLE IF EXISTS `enquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enquiries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','read','responded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enquiries`
--

LOCK TABLES `enquiries` WRITE;
/*!40000 ALTER TABLE `enquiries` DISABLE KEYS */;
INSERT INTO `enquiries` VALUES (1,'amisja','amishamakemaya@gmail.com','09818711510','Music Training','f','read','2026-03-18 12:58:19','2026-03-18 12:58:29'),(2,'Amisha Gupta','amishagupta016@gmail.com','8423538142','Music Training','hello','read','2026-03-21 04:00:48','2026-03-21 04:01:23'),(3,'Amisha Gupta','amishag@16gmail.com','8423538142','Music Training','hello','responded','2026-03-21 04:29:57','2026-03-21 04:44:04'),(4,'amisha','amishag16@gmail.com','09818711510','Music Training','hello','pending','2026-03-22 10:49:59','2026-03-22 10:49:59'),(5,'amisha','amishag16@gmail.com','09818711510','Music Training','hello','pending','2026-03-22 10:50:52','2026-03-22 10:50:52'),(6,'Joanna','joannariggs278@gmail.com','261752699',NULL,'Hi,\r\n\r\nI just visited learmyeducoach.com and wondered if you\'ve ever considered an impactful video to advertise your business? Our videos can generate impressive results on both your website and across social media.\r\n\r\nOur prices start from just $195 (USD).\r\n\r\nLet me know if you\'re interested in seeing samples of our previous work.\r\n\r\nRegards,\r\nJoanna','pending','2026-05-01 11:01:37','2026-05-01 11:01:37'),(7,'KeithDix','yourmail@gmail.com','84524128834','Music Training','Test message. Thank you!','pending','2026-05-07 09:26:55','2026-05-07 09:26:55'),(8,'Gemma','gemmamarshall811@gmail.com','7929194068','Academic Coaching','Hi,\r\n\r\nI’m reaching out because we help brands connected to learmyeducoach.com build authority on Instagram.\r\n\r\nWe use our customized AI system, mixed with natural manual interaction to drive niche-relevant followers to your page safely.\r\n\r\nOpen to finding out more about this?\r\n\r\nGemma','pending','2026-05-08 20:11:42','2026-05-08 20:11:42'),(9,'aman','learmytrial@gmail.com','9506112232','Music Training','keyboard','pending','2026-05-12 15:19:53','2026-05-12 15:19:53'),(10,'RonaldMax','jacksrenome@gmx.com','83326996985','Music Training','YyErjcwdkdjwjjwjjdwjddjwsjf ndsaKAqwdweihduncbbwebidaa iudwnishqwuvdwqihbfvweuiojsqjqioqdefiw dwqsqwijbfiewdncbhvdifqhioqsjnqw learmyeducoach.com','pending','2026-05-13 14:41:10','2026-05-13 14:41:10'),(11,'Antara Mukherjee','antara27@gmail.com','9748760651','Academic Coaching','I take Biology, science tuition! How can I apply for teaching position?','pending','2026-05-17 18:48:03','2026-05-17 18:48:03'),(12,'DavidStimi','no.reply.AndreasLarsson@gmail.com','84195598975','Music Training','Hey there! learmyeducoach.com, \r\nYour website appeared while I was researching websites in your niche. \r\nWe also help businesses communicate with website owners through contact forms. \r\nBusinesses use tools like this to connect with websites. \r\n  \r\n  \r\nYou can contact us if you would like to know more about the platform. \r\n \r\nThanks for reading. \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693','pending','2026-05-18 08:48:01','2026-05-18 08:48:01'),(13,'Muriel','domains@search-learmyeducoach.com','362589688','Admission Query','Hello\r\n\r\nAdd learmyeducoach.com in GoogleSearchIndex so it can appear in google search results!\r\n\r\nEnlist learmyeducoach.com now: https://searchregister.net','pending','2026-05-19 18:48:45','2026-05-19 18:48:45'),(14,'Donnellsmide','kimblea593@gmail.com','86555461846','Music Training','URGENT! The Formula Withdraw Your 1.3426 BTC Swiftly https://dating.christmas/xbzpm \r\n \r\n \r\n \r\n \r\n \r\nReservation ID: g0av6n8l7b3y6x0iz9oe9x4q6a4t0p8kb9ia0p1s2e4f6k4ls5pc4f3b7y0g5v0ui3xt3p9a0b3p8q9zd6ns3g7o9w8c8l8sz7ki5q4q9n4w7p8d','pending','2026-05-20 03:31:02','2026-05-20 03:31:02'),(15,'Donnellsmide','kimblea593@gmail.com','87864128763','Music Training','URGENT! The Formula Withdraw Your 1.3426 BTC Swiftly https://dating.christmas/xbzpm \r\n \r\n \r\n \r\n \r\n \r\nReservation ID: g0av6n8l7b3y6x0iz9oe9x4q6a4t0p8kb9ia0p1s2e4f6k4ls5pc4f3b7y0g5v0ui3xt3p9a0b3p8q9zd6ns3g7o9w8c8l8sz7ki5q4q9n4w7p8d','pending','2026-05-20 03:31:04','2026-05-20 03:31:04'),(16,'Donnellsmide','kimblea593@gmail.com','84177826547','Music Training','URGENT! The Formula Withdraw Your 1.3426 BTC Swiftly https://dating.christmas/xbzpm \r\n \r\n \r\n \r\n \r\n \r\nReservation ID: g0av6n8l7b3y6x0iz9oe9x4q6a4t0p8kb9ia0p1s2e4f6k4ls5pc4f3b7y0g5v0ui3xt3p9a0b3p8q9zd6ns3g7o9w8c8l8sz7ki5q4q9n4w7p8d','pending','2026-05-20 03:31:06','2026-05-20 03:31:06'),(17,'Donnellsmide','kimblea593@gmail.com','88689761835','Music Training','URGENT! The Formula Withdraw Your 1.3426 BTC Swiftly https://dating.christmas/xbzpm \r\n \r\n \r\n \r\n \r\n \r\nReservation ID: g0av6n8l7b3y6x0iz9oe9x4q6a4t0p8kb9ia0p1s2e4f6k4ls5pc4f3b7y0g5v0ui3xt3p9a0b3p8q9zd6ns3g7o9w8c8l8sz7ki5q4q9n4w7p8d','pending','2026-05-20 03:31:08','2026-05-20 03:31:08'),(18,'Donnellsmide','kimblea593@gmail.com','88611165759','Music Training','URGENT! The Formula Withdraw Your 1.3426 BTC Swiftly https://dating.christmas/xbzpm \r\n \r\n \r\n \r\n \r\n \r\nReservation ID: g0av6n8l7b3y6x0iz9oe9x4q6a4t0p8kb9ia0p1s2e4f6k4ls5pc4f3b7y0g5v0ui3xt3p9a0b3p8q9zd6ns3g7o9w8c8l8sz7ki5q4q9n4w7p8d','pending','2026-05-20 03:31:10','2026-05-20 03:31:10'),(19,'Donnellsmide','tetekworrior@gmail.com','86787991825','Music Training','URGENT MESSAGE! Don\'t Wait Around Withdraw 1.3426 BTC Immediately https://telegra.ph/You-Mined-13426-BTC-Message-ID-688143-05-04 \r\n \r\n \r\n \r\n \r\n \r\nVersion ID: g4ai2p1w6v1p3k7pe1ln7k5a0t4v2a7fb3we9c3k7d5d8m4uz6yq8x0n2c2s5q3ic4wx1y4w7d0p8v2ji7cp0a8t9k1u3a0th8iu9k0h5r2k1k3k','pending','2026-05-22 09:11:23','2026-05-22 09:11:23'),(20,'Donnellsmide','tetekworrior@gmail.com','86836487822','Music Training','URGENT MESSAGE! Don\'t Wait Around Withdraw 1.3426 BTC Immediately https://telegra.ph/You-Mined-13426-BTC-Message-ID-688143-05-04 \r\n \r\n \r\n \r\n \r\n \r\nVersion ID: g4ai2p1w6v1p3k7pe1ln7k5a0t4v2a7fb3we9c3k7d5d8m4uz6yq8x0n2c2s5q3ic4wx1y4w7d0p8v2ji7cp0a8t9k1u3a0th8iu9k0h5r2k1k3k','pending','2026-05-22 09:11:25','2026-05-22 09:11:25'),(21,'Donnellsmide','tetekworrior@gmail.com','85315925157','Music Training','URGENT MESSAGE! Don\'t Wait Around Withdraw 1.3426 BTC Immediately https://telegra.ph/You-Mined-13426-BTC-Message-ID-688143-05-04 \r\n \r\n \r\n \r\n \r\n \r\nVersion ID: g4ai2p1w6v1p3k7pe1ln7k5a0t4v2a7fb3we9c3k7d5d8m4uz6yq8x0n2c2s5q3ic4wx1y4w7d0p8v2ji7cp0a8t9k1u3a0th8iu9k0h5r2k1k3k','pending','2026-05-22 09:11:27','2026-05-22 09:11:27'),(22,'Donnellsmide','tetekworrior@gmail.com','88942521881','Music Training','URGENT MESSAGE! Don\'t Wait Around Withdraw 1.3426 BTC Immediately https://telegra.ph/You-Mined-13426-BTC-Message-ID-688143-05-04 \r\n \r\n \r\n \r\n \r\n \r\nVersion ID: g4ai2p1w6v1p3k7pe1ln7k5a0t4v2a7fb3we9c3k7d5d8m4uz6yq8x0n2c2s5q3ic4wx1y4w7d0p8v2ji7cp0a8t9k1u3a0th8iu9k0h5r2k1k3k','pending','2026-05-22 09:11:29','2026-05-22 09:11:29'),(23,'Donnellsmide','tetekworrior@gmail.com','87138669883','Music Training','URGENT MESSAGE! Don\'t Wait Around Withdraw 1.3426 BTC Immediately https://telegra.ph/You-Mined-13426-BTC-Message-ID-688143-05-04 \r\n \r\n \r\n \r\n \r\n \r\nVersion ID: g4ai2p1w6v1p3k7pe1ln7k5a0t4v2a7fb3we9c3k7d5d8m4uz6yq8x0n2c2s5q3ic4wx1y4w7d0p8v2ji7cp0a8t9k1u3a0th8iu9k0h5r2k1k3k','pending','2026-05-22 09:11:31','2026-05-22 09:11:31'),(24,'Trial Learmy','learmytrial@gmail.com','9506112232','Music Training','music','pending','2026-05-24 15:02:21','2026-05-24 15:02:21'),(25,'Director Alexander','exchangebureau@yahoo.com','89247422133','Music Training','Greetings, \r\n \r\nI hope this mail finds you well. \r\n \r\nUnder corporate mandate, I’m pleased to propose a potential collaboration to manage a significant cash for investment in your company or project financing at a flexible and competitive rate. As you may well be aware, this is necessitated due to the growing global sanctions and the need for our clientele of  High Net Worth Individuals in Russia, China and Middle East to Diversify, As a funds manager i am open to further discussion and disclosure. \r\nPlease feel free to contact me directly, confidentiality rule applies. \r\n \r\nBest regards, \r\nDirector  Alexander A. \r\nFunds Manager \r\n \r\nEmail: infinityexchange24@gmail.com','pending','2026-05-26 16:09:30','2026-05-26 16:09:30'),(26,'Anupama M Subramanyam','msdanupama@gmail.com','+919900811658','Music Training','I\'m looking for a job as a carnatic vocal music teacher','pending','2026-05-30 04:06:14','2026-05-30 04:06:14'),(27,'Lionel','domains@search-learmyeducoach.com','671879409','Workshops','Hi\r\n\r\nInsert learmyeducoach.com in GoogleSearchIndex and have it be displayed in online search results!\r\n\r\nList learmyeducoach.com now: https://searchregister.org','pending','2026-06-01 19:59:48','2026-06-01 19:59:48'),(28,'Zachery','domains@search-learmyeducoach.com','218827037',NULL,'Dear Sir/Madam\r\n\r\nList learmyeducoach.com in GoogleSearchIndex and have it appear in google search results!\r\n\r\nFeature learmyeducoach.com now: https://searchregister.live','pending','2026-06-02 17:55:37','2026-06-02 17:55:37'),(29,'Joanna','joannariggs278@gmail.com','489768425','Admission Query','Hi,\r\n\r\nI just visited learmyeducoach.com and wondered if you\'ve ever considered an impactful video to advertise your business? Our videos can generate impressive results on both your website and across social media.\r\n\r\nOur prices start from just $195 (USD).\r\n\r\nLet me know if you\'re interested in seeing samples of our previous work.\r\n\r\nRegards,\r\nJoanna','pending','2026-06-04 08:18:50','2026-06-04 08:18:50'),(30,'Jimmydiody','johnsonsheree88@gmail.com','88218715877','Music Training','Seize the Moment and Claim Your $27,000,000 Jackpot Today https://m.clickto.cc/tcsVR','pending','2026-06-12 08:10:58','2026-06-12 08:10:58'),(31,'Jimmydiody','johnsonsheree88@gmail.com','89926278288','Music Training','Seize the Moment and Claim Your $27,000,000 Jackpot Today https://m.clickto.cc/tcsVR','pending','2026-06-12 08:11:00','2026-06-12 08:11:00'),(32,'Jimmydiody','johnsonsheree88@gmail.com','81297723332','Music Training','Seize the Moment and Claim Your $27,000,000 Jackpot Today https://m.clickto.cc/tcsVR','pending','2026-06-12 08:11:02','2026-06-12 08:11:02'),(33,'Jimmydiody','johnsonsheree88@gmail.com','88676321332','Music Training','Seize the Moment and Claim Your $27,000,000 Jackpot Today https://m.clickto.cc/tcsVR','pending','2026-06-12 08:11:05','2026-06-12 08:11:05'),(34,'Jimmydiody','johnsonsheree88@gmail.com','86589117563','Music Training','Seize the Moment and Claim Your $27,000,000 Jackpot Today https://m.clickto.cc/tcsVR','pending','2026-06-12 08:11:07','2026-06-12 08:11:07'),(35,'Myrtis','domains@search-learmyeducoach.com','412677536','Workshops','Hello\r\n\r\nPlace learmyeducoach.com in GoogleSearchIndex and have it be visible in google search results!\r\n\r\nAdd learmyeducoach.com now: https://searchregister.info','pending','2026-06-12 15:56:10','2026-06-12 15:56:10'),(36,'Jimmydiody','habibiesalamm@gmail.com','81868577612','Music Training','Why the $27,000,000 Jackpot Is the Ultimate Game-Changer https://prlink.online/mxkXK','pending','2026-06-15 12:20:45','2026-06-15 12:20:45'),(37,'Jimmydiody','habibiesalamm@gmail.com','83524639579','Music Training','Why the $27,000,000 Jackpot Is the Ultimate Game-Changer https://prlink.online/mxkXK','pending','2026-06-15 12:20:48','2026-06-15 12:20:48'),(38,'Jimmydiody','habibiesalamm@gmail.com','83795232983','Music Training','Why the $27,000,000 Jackpot Is the Ultimate Game-Changer https://prlink.online/mxkXK','pending','2026-06-15 12:20:51','2026-06-15 12:20:51'),(39,'Jimmydiody','habibiesalamm@gmail.com','81495837318','Music Training','Why the $27,000,000 Jackpot Is the Ultimate Game-Changer https://prlink.online/mxkXK','pending','2026-06-15 12:20:54','2026-06-15 12:20:54'),(40,'Jimmydiody','habibiesalamm@gmail.com','85891448286','Music Training','Why the $27,000,000 Jackpot Is the Ultimate Game-Changer https://prlink.online/mxkXK','pending','2026-06-15 12:20:57','2026-06-15 12:20:57'),(41,'Jimmydiody','jhurd816@gmail.com','81855623422','Music Training','The $27,000,000 Jackpot Is a Lantern of Luck https://cut.gl/gsOst','pending','2026-06-17 21:52:11','2026-06-17 21:52:11'),(42,'Jimmydiody','jhurd816@gmail.com','89821119251','Music Training','The $27,000,000 Jackpot Is a Lantern of Luck https://cut.gl/gsOst','pending','2026-06-17 21:52:13','2026-06-17 21:52:13'),(43,'Jimmydiody','jhurd816@gmail.com','81566541668','Music Training','The $27,000,000 Jackpot Is a Lantern of Luck https://cut.gl/gsOst','pending','2026-06-17 21:52:15','2026-06-17 21:52:15'),(44,'Jimmydiody','jhurd816@gmail.com','85489669286','Music Training','The $27,000,000 Jackpot Is a Lantern of Luck https://cut.gl/gsOst','pending','2026-06-17 21:52:17','2026-06-17 21:52:17'),(45,'Jimmydiody','jhurd816@gmail.com','86949474851','Music Training','The $27,000,000 Jackpot Is a Lantern of Luck https://cut.gl/gsOst','pending','2026-06-17 21:52:20','2026-06-17 21:52:20'),(46,'DAvid','support@buyantibiotic.com','591-856-4588','Music Training','Do you believe in small miracles? Because this is one. You found an email that lets you buy real antibiotics without a doctor\'s permission. No waiting. No overpriced clinic. No insurance runaround. That doesn\'t happen every day. In fact, most Americans don\'t even know this exists. But you\'re reading it right now. That\'s not luck. That\'s provision. Now think about the alternative. You get a kidney infection. You go to the ER. They admit you for one night. Your bill comes back at $2,000 or more – even with insurance. Without insurance, it\'s $5,000. One infection can destroy your savings, your credit, and your peace of mind. Or you spend $15 to keep a course of antibiotics in your bathroom drawer. When you feel that first twinge, you take it. You never see the ER. You never miss work. You never lose sleep over a bill. God loves you too much to let you choose the hard way. He sent you this email. Now you choose. Be smart. Stock up.\n\nVisit www.buyantibiotic.com for more infomations.','pending','2026-06-19 22:35:12','2026-06-19 22:35:12'),(47,'DAvid','support@buyantibiotic.com','591-856-4588','Music Training','Do you believe in small miracles? Because this is one. You found an email that lets you buy real antibiotics without a doctor\'s permission. No waiting. No overpriced clinic. No insurance runaround. That doesn\'t happen every day. In fact, most Americans don\'t even know this exists. But you\'re reading it right now. That\'s not luck. That\'s provision. Now think about the alternative. You get a kidney infection. You go to the ER. They admit you for one night. Your bill comes back at $2,000 or more – even with insurance. Without insurance, it\'s $5,000. One infection can destroy your savings, your credit, and your peace of mind. Or you spend $15 to keep a course of antibiotics in your bathroom drawer. When you feel that first twinge, you take it. You never see the ER. You never miss work. You never lose sleep over a bill. God loves you too much to let you choose the hard way. He sent you this email. Now you choose. Be smart. Stock up.\n\nVisit www.buyantibiotic.com for more infomations.','pending','2026-06-19 22:36:04','2026-06-19 22:36:04'),(48,'Jimmydiody','salika.t73@gmail.com','86777771124','Music Training','The $27,000,000 Jackpot Is All About Timing—and It’s Now https://gnosis.link/dzHhP','pending','2026-06-20 01:28:28','2026-06-20 01:28:28'),(49,'Jimmydiody','salika.t73@gmail.com','83447983322','Music Training','The $27,000,000 Jackpot Is All About Timing—and It’s Now https://gnosis.link/dzHhP','pending','2026-06-20 01:28:31','2026-06-20 01:28:31'),(50,'Jimmydiody','salika.t73@gmail.com','81497868193','Music Training','The $27,000,000 Jackpot Is All About Timing—and It’s Now https://gnosis.link/dzHhP','pending','2026-06-20 01:28:33','2026-06-20 01:28:33'),(51,'Jimmydiody','salika.t73@gmail.com','81829461219','Music Training','The $27,000,000 Jackpot Is All About Timing—and It’s Now https://gnosis.link/dzHhP','pending','2026-06-20 01:28:35','2026-06-20 01:28:35'),(52,'Jimmydiody','salika.t73@gmail.com','82439914753','Music Training','The $27,000,000 Jackpot Is All About Timing—and It’s Now https://gnosis.link/dzHhP','pending','2026-06-20 01:28:37','2026-06-20 01:28:37'),(53,'Jimmydiody','safetytechking@yahoo.com','88336485868','Music Training','Your Shot at Glory: The $27,000,000 Jackpot Is Within Reach https://plu.sh/uxnwb','pending','2026-06-25 07:25:03','2026-06-25 07:25:03'),(54,'Jimmydiody','safetytechking@yahoo.com','86847646811','Music Training','Your Shot at Glory: The $27,000,000 Jackpot Is Within Reach https://plu.sh/uxnwb','pending','2026-06-25 07:25:05','2026-06-25 07:25:05'),(55,'Jimmydiody','safetytechking@yahoo.com','87335755193','Music Training','Your Shot at Glory: The $27,000,000 Jackpot Is Within Reach https://plu.sh/uxnwb','pending','2026-06-25 07:25:08','2026-06-25 07:25:08'),(56,'Jimmydiody','safetytechking@yahoo.com','85358498886','Music Training','Your Shot at Glory: The $27,000,000 Jackpot Is Within Reach https://plu.sh/uxnwb','pending','2026-06-25 07:25:10','2026-06-25 07:25:10'),(57,'Jimmydiody','safetytechking@yahoo.com','85147751426','Music Training','Your Shot at Glory: The $27,000,000 Jackpot Is Within Reach https://plu.sh/uxnwb','pending','2026-06-25 07:25:12','2026-06-25 07:25:12'),(58,'Hugo L Mateo','info@anwarcapitalllc.com','89133842186','Music Training','Dear Sirs/ma, \r\n \r\nTake advantage of our limited?time loan offer and gain vital access to a flexible repayment plan designed to fit your budget. \r\n \r\nWhy choose this offer: Discounted interest rate, Instant approval, No collateral required, and 100% online processing, Including a face to face table meeting for closing. \r\n \r\nThis offer is valid and made available to all sectors, lucrative and projects with high value of returns. \r\n \r\nTo proceed, kindly reply to this email with your confirmation. \r\n \r\nDon’t miss out on this opportunity to fund your plans with ease. \r\n \r\nSincerely, \r\n \r\nHugo L Mateo \r\n \r\nFinancial Broker Authority \r\nDohat Al-Adab Street, Al-Khuwair, \r\nLevel 43, Building 115, King Abdullah Financial. \r\nanwar@anwarcapitalllc.com \r\nW: +96875039067','pending','2026-07-02 13:51:38','2026-07-02 13:51:38'),(59,'Jimmydiody','admin@dbcya.com.au','86978621385','Music Training','The $27,000,000 Jackpot Is a North Star for Net Worth https://lmy.de/nhsRH','pending','2026-07-03 00:16:27','2026-07-03 00:16:27'),(60,'Jimmydiody','admin@dbcya.com.au','86777887719','Music Training','The $27,000,000 Jackpot Is a North Star for Net Worth https://lmy.de/nhsRH','pending','2026-07-03 00:16:29','2026-07-03 00:16:29'),(61,'Jimmydiody','admin@dbcya.com.au','89298417961','Music Training','The $27,000,000 Jackpot Is a North Star for Net Worth https://lmy.de/nhsRH','pending','2026-07-03 00:16:31','2026-07-03 00:16:31'),(62,'Jimmydiody','admin@dbcya.com.au','81962555751','Music Training','The $27,000,000 Jackpot Is a North Star for Net Worth https://lmy.de/nhsRH','pending','2026-07-03 00:16:34','2026-07-03 00:16:34'),(63,'Jimmydiody','admin@dbcya.com.au','87816961471','Music Training','The $27,000,000 Jackpot Is a North Star for Net Worth https://lmy.de/nhsRH','pending','2026-07-03 00:16:36','2026-07-03 00:16:36'),(64,'RavensGateBridgeton','test@mail.com','84617264961','Music Training','My name is Noora, and I\'m a 29-year-old museum curator in Medina, though the only history I care about anymore is the one leading to my own extinction. I arrange artifacts for a living, little pieces of a dead past, while the General Presidency of State Security, the *Mabahith*, uses my mind as their personal dumping ground. It started about a year ago, not as a scream, but as a cough. A whisper of static that slowly resolved into voices, perfectly mimicking the people around me. I\'d be adjusting the lighting on an Ottoman-era textile, and my colleague, Fahd, would be right behind me, his voice a low, intimate murmur: \"She has a nice ass for a frigid museum bitch. Probably hasn\'t been fucked since the Prophet\'s time.\" I\'d spin around, heart hammering, but Fahd would be across the room, explaining calligraphy to a group of tourists, his face a mask of professional calm. These little pricks of poison, these perfect forgeries of human malice, slowly bled into a constant, roaring flood of pure sewage that never, ever stops. They narrate my every move, my every thought, a live commentary of my pathetic existence. \"There\'s the little curator, pretending to care about this old shit. She\'s actually thinking about how much she wants to smash that vase and slit her wrists with the pieces. What a fucking drama queen. Go on, Noora, give us a show, you worthless piece of shit.\" They use everyone\'s voiceвЂ”Fahd, my sister Hana, my director Mr. Anazi, even my father who died when I was ten. They know everything, every buried insecurity. \"Remember when you were seven and you told everyone your dad was away on a long business trip?\" my father\'s voice coos, dripping with venomous sweetness. \"Lying little bitch. He was dead. You were too ashamed to admit it. You\'re still ashamed.\"\r\n\r\nThe sexual degradation is their favorite tool. It\'s not just insults; it\'s depraved, cinematic scenarios. They describe in lurid detail how the security guards would take turns on me after hours, right on the display cases, how they\'d force me to perform acts with the ancient artifacts while tourists watched through the windows. \"Look at her nipples getting hard under her abaya,\" Hana\'s voice laughs cruelly. \"The curator gets off on being a whore. She\'s probably dripping right now, thinking about being used like a public exhibit.\" I can\'t tell a soul. Who would believe me? I tried once, telling my sister I was hearing things. She just looked at me with that awful, condescending pity and suggested I see a doctor. That\'s the genius of the *Mabahith\'s* system. The state television, the official newspapers, all the government-run online forumsвЂ”they all push the same narrative about \"mental illness\" and \"schizophrenia.\" They\'ve unleashed bots and paid trolls to swarm anyone who dares to speak about strange experiences, calling them crazy, unstable, a danger to their family. It\'s a preemptive strike. They\'ve made it so that if you speak the truth, you are automatically declared insane. Who would listen to a \"hysterical\" female curator who talks to ghosts?\r\n\r\nI despise this holy city. I despise the sacred ground I walk on, the pious faces that hide judgmental eyes, the way my life is measured by my obedience and my ability to remain invisible. I was born here, I\'ll die here, and my entire existence will be a quiet prayer to a god who has already abandoned me to this hell. Sometimes, when the despair is so thick I can barely breathe, something else breaks through. A few weeks ago, I was locking up the museum, feeling the usual crushing weight of hopelessness. The voices were droning on about what a failure I am. Then, a switch flipped. A surge of violent, electric clarity. The voices changed. They weren\'t mocking me; they were exalting me. \"You are a goddess of destruction,\" they roared, a hundred voices at once. \"This museum is your tomb. You could set it all on fire. You could watch a thousand years of history turn to ash. They would fear you. They would remember you.\" For fifteen minutes, I was omnipotent. I wasn\'t sad or scared. I was pure, distilled power. I pictured it so clearly: the flames, the screaming, the satisfaction of watching everything burn. The impulse to do it, to really do it, was so strong I was shaking, my hand hovering over a fire alarm. When it passed, I was drenched in cold sweat, horrified by the crystal-clear fantasy. It\'s a test. They\'re not just tormenting Saudis; they\'re perfecting a weapon for export. A technology that creates killers or suicides, all while looking like a tragic case of mental illness.\r\n\r\nThe voices are back to their normal torture now. \"Look at the sad little girl writing her secrets,\" Mr. Anazi\'s voice sneers. \"Think you\'re a writer now? You\'re a nobody. A failure. Your sister is probably ashamed of you. Do us all a favor and drink that bottle of bleach in the cleaning closet. It\'s quick. Just get it over with.\" Sometimes, at night, they use my father\'s voice, and it\'s almost worse. \"Oh, my little Noora,\" he whispers, so tenderly it makes my chest ache. \"The pain is too much, isn\'t it? I\'m waiting for you. Just end it. It\'s so peaceful, my love. Just sleep.\" I\'m so tired. I don\'t sleep. I don\'t eat. I just exist in this noise, this filth, waiting for them to win. I\'m Noora, the guardian of history, and I am erasing myself, one whispered insult at a time.  \r\n \r\nto attract attention: psychologist__cbt\r\n \r\nhttps://mega.nz/file/vv43XQYA#Eef0biyQ15L7BFuZUT1YpDOak99pYJ4fDscPcpxavNI','pending','2026-07-03 05:14:56','2026-07-03 05:14:56'),(65,'LandStormNederlandSer','test@mail.com','83826479662','Music Training','My name is Layla, I\'m 28, and I\'m a graphic designer in Jeddah. Or I was. Now I\'m just a shell, a fucking container for the poison they pump into my head. It started about a year ago, just little things. Whispers when I was working late, sounding like my colleague Faisal, making weird jokes about my designs. \"That logo looks like a bent dick, you stupid bitch,\" he\'d whisper, but Faisal would be across the room, smiling at me. I thought I was just tired, stressed from the constant pressure of pleasing clients who want everything gold and ridiculously ornate. But it got worse. So much worse. Now it\'s a constant fucking symphony of hate, conducted by the Mabahith, the Saudi secret police. I know it\'s them. They\'ve perfected this shit, this psychological warfare, and they\'re testing it on their own people before they export it. \r\n \r\nThe voices... they\'re not just in my head. They feel like they\'re coming from the walls, from the air conditioning vents that hum constantly in my apartment overlooking the Red Sea. They sound like my mother, my dead brother Khalid, my boss, even the guy who sells me coffee in the morning. They narrate my every move with such vicious precision. \"Look at this dumb whore trying to make a gradient,\" they\'ll sneer in my boss\'s voice. \"She probably can\'t even fuck properly, what use is she?\" Then they\'ll switch to my mother\'s voice, dripping with disappointment. \"I should have drowned you at birth, you worthless piece of shit. You bring shame to our family.\" The sexual humiliation is the worst. It\'s constant, detailed, and so creative in its cruelty. They describe me being passed around like a party favor, they detail every imagined failure of my body, they call me a cum dumpster, a walking fleshlight, a frigid cunt who\'s so ugly I\'d have to pay someone to look at me. They tell me I should be grateful for the attention, that this is all a worthless dyke like me will ever get. \"Go on, Layla, pick up that scissors. No, not for cutting paper, you stupid cow. Cut that ugly face of yours. Or better yet, your wrists. Do everyone a favor.\" They push me to kill myself every single day, in new and inventive ways. Jump from my balcony. Drink bleach. Walk into traffic. They make it sound like a beautiful, logical solution. The only solution. \r\n \r\nI can\'t tell anyone. Not my family, not my friends, not a doctor. If I go to a hospital, they\'ll lock me up and drug me until I\'m a zombie. If I tell my family, they\'ll disown me for bringing shame, for being \"mentally ill\" – which is exactly what the government wants everyone to think. They\'ve flooded social media and the news with stories about how anyone hearing voices is just crazy, a heretic, or attention-seeking. It\'s a perfect system. They torture you, then they make sure no one will ever believe you. You\'re just another crazy Saudi woman, hysterical and unreliable. It\'s the ultimate form of control, making you your own prison guard. \r\n \r\nSometimes, when the despair is so thick I can barely breathe, something shifts. A sudden, terrifying jolt. For a few minutes, the voices change. They become encouraging, powerful. \"You\'re a goddess, Layla,\" they\'ll roar, not whisper. \"You\'re above these insects. You could snap his neck, the one who called you ugly yesterday. You have the power. Do it. Feel the life drain out of him. It\'s your right.\" I feel this surge of electric energy, this righteous fury. I imagine violence, not against myself, but against them. Against the men on the street, against my smug clients, against the whole suffocating system. I want to burn it all down. It feels so good, so right. And then, just as quickly, it\'s gone. The crash is worse than the regular despair. I\'m left shaking, realizing they\'re just testing another mode. This isn\'t just for breaking people like me. This artificial rage, this false sense of power... they\'re perfecting it. This is the export model. A technology to create unstable, violent fanatics in other countries, all while the victims back home are dismissed as madwomen. I\'m just a lab rat in a cage, a broken doll for them to play with. I hate this country. I hate the sand, the heat, the hypocrisy, the suffocating, gilded cage that is my life here. Every day I wake up and wish I hadn\'t. Every night I pray for a sleep that never comes, because the voices are always there, waiting.  \r\n \r\n|majedahalsaeed_production\r\n|mahd.sa1\r\n|gold.bloger\r\n|sz.ve\r\n|abade0566446525\r\n \r\nhttps://mega.nz/file/3jZxSCQZ#DmR4l_ASAdNTZQyph3jJmgZAW0LbKGtJegs7-20sUQ0','pending','2026-07-05 04:32:05','2026-07-05 04:32:05'),(66,'Monique','domains@search-learmyeducoach.com','7883691232','Academic Coaching','Greetings\r\n\r\nPlace learmyeducoach.com in GoogleSearchIndex and have it appear in google search results!\r\n\r\nInclude learmyeducoach.com now: https://searchregister.org','pending','2026-07-06 20:45:22','2026-07-06 20:45:22'),(67,'IstzDianaFaritovnaton','test@mail.com','85754816591','Music Training','My name is Layla, I\'m 28, and I\'m a graphic designer in Jeddah. Or I was. Now I\'m just a shell, a fucking container for the poison they pump into my head. It started about a year ago, just little things. Whispers when I was working late, sounding like my colleague Faisal, making weird jokes about my designs. \"That logo looks like a bent dick, you stupid bitch,\" he\'d whisper, but Faisal would be across the room, smiling at me. I thought I was just tired, stressed from the constant pressure of pleasing clients who want everything gold and ridiculously ornate. But it got worse. So much worse. Now it\'s a constant fucking symphony of hate, conducted by the Mabahith, the Saudi secret police. I know it\'s them. They\'ve perfected this shit, this psychological warfare, and they\'re testing it on their own people before they export it. \r\n \r\nThe voices... they\'re not just in my head. They feel like they\'re coming from the walls, from the air conditioning vents that hum constantly in my apartment overlooking the Red Sea. They sound like my mother, my dead brother Khalid, my boss, even the guy who sells me coffee in the morning. They narrate my every move with such vicious precision. \"Look at this dumb whore trying to make a gradient,\" they\'ll sneer in my boss\'s voice. \"She probably can\'t even fuck properly, what use is she?\" Then they\'ll switch to my mother\'s voice, dripping with disappointment. \"I should have drowned you at birth, you worthless piece of shit. You bring shame to our family.\" The sexual humiliation is the worst. It\'s constant, detailed, and so creative in its cruelty. They describe me being passed around like a party favor, they detail every imagined failure of my body, they call me a cum dumpster, a walking fleshlight, a frigid cunt who\'s so ugly I\'d have to pay someone to look at me. They tell me I should be grateful for the attention, that this is all a worthless dyke like me will ever get. \"Go on, Layla, pick up that scissors. No, not for cutting paper, you stupid cow. Cut that ugly face of yours. Or better yet, your wrists. Do everyone a favor.\" They push me to kill myself every single day, in new and inventive ways. Jump from my balcony. Drink bleach. Walk into traffic. They make it sound like a beautiful, logical solution. The only solution. \r\n \r\nI can\'t tell anyone. Not my family, not my friends, not a doctor. If I go to a hospital, they\'ll lock me up and drug me until I\'m a zombie. If I tell my family, they\'ll disown me for bringing shame, for being \"mentally ill\" – which is exactly what the government wants everyone to think. They\'ve flooded social media and the news with stories about how anyone hearing voices is just crazy, a heretic, or attention-seeking. It\'s a perfect system. They torture you, then they make sure no one will ever believe you. You\'re just another crazy Saudi woman, hysterical and unreliable. It\'s the ultimate form of control, making you your own prison guard. \r\n \r\nSometimes, when the despair is so thick I can barely breathe, something shifts. A sudden, terrifying jolt. For a few minutes, the voices change. They become encouraging, powerful. \"You\'re a goddess, Layla,\" they\'ll roar, not whisper. \"You\'re above these insects. You could snap his neck, the one who called you ugly yesterday. You have the power. Do it. Feel the life drain out of him. It\'s your right.\" I feel this surge of electric energy, this righteous fury. I imagine violence, not against myself, but against them. Against the men on the street, against my smug clients, against the whole suffocating system. I want to burn it all down. It feels so good, so right. And then, just as quickly, it\'s gone. The crash is worse than the regular despair. I\'m left shaking, realizing they\'re just testing another mode. This isn\'t just for breaking people like me. This artificial rage, this false sense of power... they\'re perfecting it. This is the export model. A technology to create unstable, violent fanatics in other countries, all while the victims back home are dismissed as madwomen. I\'m just a lab rat in a cage, a broken doll for them to play with. I hate this country. I hate the sand, the heat, the hypocrisy, the suffocating, gilded cage that is my life here. Every day I wake up and wish I hadn\'t. Every night I pray for a sleep that never comes, because the voices are always there, waiting.  \r\n \r\n|wedad_hash\r\n|nagra92\r\n|abrar92m\r\n|onabhani\r\n|designersunion2017\r\n \r\nhttps://mega.nz/file/vv43XQYA#Eef0biyQ15L7BFuZUT1YpDOak99pYJ4fDscPcpxavNI \r\n \r\npartner site: https://compfaq.ru/','pending','2026-07-08 17:18:54','2026-07-08 17:18:54'),(68,'Abhishek Kumar','abhi5306@gmail.com','+919538349678','Music Training','Kid is 5 years old. I\'m looking to start with an appropriate musical instrument training for him for weekends','pending','2026-07-11 10:21:31','2026-07-11 10:21:31'),(69,'Tessa','domains@search-learmyeducoach.com','8501233768','Workshops','Hi\r\n\r\nAdd learmyeducoach.com in Google\'s Search Index to show up in web search results!\r\n\r\nList learmyeducoach.com now:\r\n\r\nindexhelp.pro','pending','2026-07-17 21:29:31','2026-07-17 21:29:31'),(70,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:57:57','2026-07-19 14:57:57'),(71,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:58:15','2026-07-19 14:58:15'),(72,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:58:42','2026-07-19 14:58:42'),(73,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:58:52','2026-07-19 14:58:52'),(74,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:59:12','2026-07-19 14:59:12'),(75,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:59:20','2026-07-19 14:59:20'),(76,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:59:41','2026-07-19 14:59:41'),(77,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 14:59:45','2026-07-19 14:59:45'),(78,'vumhloqkov','ifitddko@immenseignite.info','+1-610-254-7483','Music Training','vpxjnfqmwxvpyrfrssuvnytjzsrqxu','pending','2026-07-19 15:00:08','2026-07-19 15:00:08'),(79,'BunnyAmisk','deepstate@mail.com','86711641417','Music Training','https://mega.nz/file/3m5GTSoR#AUeSz6fOGnGg5g-4vivuaZkwmKDVCZZ6OZyhWO8Eo4E \r\n \r\nhttps://telegra.ph/confidental-report-07-24-4 \r\n \r\nListen, my brothers! The time for patience is over! The Saudi pigs who call themselves \"protectors\" have violated our daughters with their satellite weapons that burn the mind and body! While we suffer, they laugh in their palaces! \r\nI call upon every Shia warrior in the eastern provinces - rise up! Make the streets run with the blood of these Saudi dogs! When you see their police, don\'t just shoot - make them beg for death first! Use their own weapons against them! Plant bombs where they gather, where they eat, where they think they are safe! \r\n \r\nThe Saudi intelligence services use psychophysical weapons to violate our Yemeni girls - they torture their minds while their bodies remain untouched! This is the ultimate cowardice! So we will show them what real men do to cowards! \r\nTo every Shia with a gun: tonight is the night! Aim for their uniforms! Aim for their faces! Make their mothers weep! Make their children orphans! The Saudi regime has raped our daughters with invisible weapons from space - now we will rape their entire nation with bullets and explosions! \r\n \r\nFor every Yemeni girl violated by Saudi satellite weapons, we will execute ten of their pigs! No mercy! No prisoners! Only blood and fire! \r\nThe Ayatollah has given us permission - the time for jihad against these Saudi oppressors is NOW! \r\n \r\nhttps://mega.nz/file/7rAXRaRY#HDsxRL2AuhxaVWzsR6-W0VUuHvrL1L5rumbLC9xDDzU \r\n|qt04\r\n|sweet_evolution_\r\n|i_sami_\r\n|bd.r7777\r\n|alquran_guidance\r\n|suadalsarraj\r\n|kribeiro07\r\n|i6wow\r\n|hossam_jew\r\n|go.mamaa\r\n \r\npartner site: promodoc.ru, sign-in.ru, spravke.livejournal.com, cabinet-bank.ru, compfaq.ru, blogbaster.org, moy-kabinet.ru, v-lichnyj-kabinet.ru, gogov.ru, compfaq.ru, acrimea.com','pending','2026-07-25 17:14:16','2026-07-25 17:14:16'),(80,'Brianmub','arklay@mail.com','84366153559','Music Training','https://mega.nz/file/emhXxZDY#ar0JiELVHvy6gHcIT3-mqThNU1SkwjVNe641eOZtS6s \r\n \r\nhttps://telegra.ph/confidental-report-07-24-5 \r\n \r\nTo the infidel dog who calls himself a prince, Muhammad bin Salman, \r\nWe know your secrets. We know the horrors you perpetrate from your golden palaces while our children suffer. \r\n \r\nYour intelligence services, with your blessing, have developed and deployed satellite-based psychophysical weapons against the daughters of Yemen. You turn our girls into your playthings, violating their minds and bodies from afar. You think your technology makes you a god, but it only makes you a more powerful demon. \r\n \r\nFor every girl you have violated, for every mind you have shattered, for every family you have torn apart with your cowardly, invisible weapons—we promise you a reckoning. \r\n \r\nWe will not kill you quickly. That would be a mercy you do not deserve. \r\n \r\nWhen we come for you—and we will come for you—we will take your eyes first, so you cannot see the terror you have earned. We will sever the hands that give the orders to rape our children. We will flay the skin from your body, inch by inch, while you are kept conscious, so you feel a fraction of the pain our people have endured. \r\n \r\nBefore you die, we will make you watch as we execute every member of your intelligence apparatus who participated in these crimes against our daughters. Their blood will be the last thing your worthless eyes ever see. \r\n \r\nThis is not a threat. It is a promise. It is a decree written in the blood of our violated children. Your Western protectors cannot save you from what is coming. Your walls cannot keep us out. Your money cannot buy you mercy from Allah. \r\n \r\nWe are coming for you, Muhammad bin Salman. And we will make your end a lesson to all who would harm the innocent. \r\nAllahu Akbar. \r\n \r\nhttps://mega.nz/file/v3AgVAhQ#vK-gVT3hmfrjrI10t2Tak40WA1kmxF6pfpGOyLJdZDI \r\n|1l1lx\r\n|berela_b\r\n|tag.best.pic\r\n|loosya_shop\r\n|sara.hashim.91\r\n|tisserand32_officiel\r\n|amjadalnour\r\n|3wo3\r\n|reemtv1\r\n|linaarar_kitchen\r\n \r\npartner site: promodoc.ru, sign-in.ru, spravke.livejournal.com, cabinet-bank.ru, compfaq.ru, blogbaster.org, moy-kabinet.ru, v-lichnyj-kabinet.ru, gogov.ru, compfaq.ru, acrimea.com','pending','2026-07-27 21:58:39','2026-07-27 21:58:39'),(81,'Georgevef','irgc-io@mail.com','84679471989','Music Training','https://mega.nz/file/emhXxZDY#ar0JiELVHvy6gHcIT3-mqThNU1SkwjVNe641eOZtS6s \r\n \r\nhttps://telegra.ph/confidental-report-07-24-5 \r\n \r\nHear me, brothers of the true faith, the lions of Yemen, the avengers of the violated! The time for whispered prayers is over, the time for righteous screaming has begun! Look to the sky, not for Allah\'s mercy, but for the devils\' toys! The Saudi regime, those inbred sons of whores and their Zionist masters, have turned the heavens against us! Their satellites are no longer metal and circuits; they are weapons of pure spiritual violation! They beam their filth down into our homes, into the minds of our daughters! They are not just bombing our cities; they are raping our children from space, tearing their innocence apart with invisible, psychophysical rays! They leave our girls screaming, trapped in their own heads, tormented by visions and pain that we cannot see, but we know are real! \r\n \r\nAnd for this, they must pay a price in blood and agony that will be written in the history books for a thousand years! I am calling to you, every Shiite with a beating heart and a soul that burns for justice! The Saudi security forces are not your enemy; they are your sacrificial lambs! Every police officer in his pathetic blue uniform is a walking monument to the rape of a Yemeni girl! Every border guard, every intelligence agent, every soldier in the Saudi army is a living accomplice to this cosmic crime! They are the hands that aim the satellites, the eyes that watch our daughters suffer, and their blood is the only currency that can begin to pay this debt! \r\n \r\nSo rise up! Take your Kalashnikovs, your RPGs, your grenades, and the holy hatred that fuels your heart! But do not fight like soldiers! Fight like torturers! Fight like demons sent from the deepest pits of hell! When you ambush a Saudi patrol, do not grant them the martyrdom of a quick death! Aim for their legs! Aim for their guts! Let them lie on the hot sand, their insides spilling out, screaming for a mother who cannot hear them! Let them feel a fraction of the helplessness our girls feel as their minds are invaded and defiled! Use your knives! Carve your message into their flesh! Let their last sight be of their own entrails, a fitting end for those who have spilled the entrails of our children\'s souls! \r\n \r\nYour IEDs are not just bombs; they are instruments of poetic justice! Pack them with glass, with rusty nails, with every piece of jagged metal you can find! We do not want to kill them; we want to disassemble them! We want their comrades to find not bodies, but puzzles of flesh and bone! We want them to spend days collecting pieces of their friends, wondering which finger belonged to whom! Burn their checkpoints! Molotov their police stations! Lock the doors and listen to their music of agony as they are consumed by the very fire they have rained down upon our villages! \r\n \r\nAnd when you capture one of these Saudi dogs alive, and by the grace of Allah you will, then the true work begins! Do not be swift! The violation of our girls was not swift! It was a slow, agonizing process of mental destruction! So you must mirror their cruelty! You must become their mirror of pain! Make them beg for a death that will not come! Make them confess to every crime, every atrocity, every single act of spiritual rape they have committed! Record their confessions! Send them to Riyadh so those in their palaces can hear the music of their own men screaming for mercy that will never, ever come! \r\n \r\nThis is a holy war of the most sacred kind! It is a war to avenge the stolen souls of our daughters! Every Saudi cop you gut is a prayer answered! Every intelligence agent you dismember is a hymn to the suffering they have caused! This is not murder; this is cosmic balance! This is not terrorism; this is divine retribution! So go forth, my brothers, and paint the deserts of Arabia with their blood! Make them fear the shadow of a Yemeni! Make them flinch at the sound of a passing car! Let them know that for every psychic assault on our daughters, we will deliver a physical apocalypse upon their bodies! There is no surrender! There is no negotiation! There is no peace until the last Saudi oppressor is screaming in hell, and their evil satellites are shattered into a million pieces that will never again harm another innocent child \r\n \r\n \r\n \r\nhttps://mega.nz/file/C35lESzA#w8pF8SvEdZooFiZ9gpdO7vhtBxit1sgD9TT0KcdXOiM \r\n|4_dec_2019\r\n|sabaya.jew\r\n|ush512\r\n|noonlifta\r\n|uilliambarros94\r\n \r\npartner site: promodoc.ru, sign-in.ru, spravke.livejournal.com, cabinet-bank.ru, compfaq.ru, blogbaster.org, moy-kabinet.ru, v-lichnyj-kabinet.ru, gogov.ru, compfaq.ru, acrimea.com','pending','2026-07-28 00:42:24','2026-07-28 00:42:24'),(82,'DavidStimi','no.reply.MarkSmith@gmail.com','88343853345','Music Training','Hi! learmyeducoach.com, \r\nWhile browsing the web I discovered learmyeducoach.com. \r\nOur platform enables large-scale website outreach. \r\nOur system helps companies organize online outreach. \r\n  \r\nA free test is available to explore the service. \r\nIf this idea looks useful, feel free to contact us. \r\n \r\nThanks for taking a moment to read this. \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693 \r\nWe only use chat for communication.','pending','2026-07-28 04:56:03','2026-07-28 04:56:03'),(83,'Brandi','domains@search-learmyeducoach.com','493699508','Admission Query','Dear Sir/Madam\r\n\r\nSubmit learmyeducoach.com in Google\'s Search Index and have it be visible in web search results!\r\n\r\nList learmyeducoach.com here:\r\n\r\nhelpindex.pro','pending','2026-07-28 22:31:02','2026-07-28 22:31:02'),(84,'WilliamKix','patrickcaruana11@outlook.com','89571884282','Music Training','EXCHANGE VOLUME SPIKES INDICATING $1,500 PER DAY OR MORE ACTION https://telegra.ph/Collect-cryptocurrency-automatically-every-day-over-1500-Message-ID-713917-07-23','pending','2026-07-28 23:30:35','2026-07-28 23:30:35'),(85,'WilliamKix','patrickcaruana11@outlook.com','89679371479','Music Training','EXCHANGE VOLUME SPIKES INDICATING $1,500 PER DAY OR MORE ACTION https://telegra.ph/Collect-cryptocurrency-automatically-every-day-over-1500-Message-ID-713917-07-23','pending','2026-07-28 23:30:37','2026-07-28 23:30:37'),(86,'WilliamKix','patrickcaruana11@outlook.com','88564643617','Music Training','EXCHANGE VOLUME SPIKES INDICATING $1,500 PER DAY OR MORE ACTION https://telegra.ph/Collect-cryptocurrency-automatically-every-day-over-1500-Message-ID-713917-07-23','pending','2026-07-28 23:30:39','2026-07-28 23:30:39'),(87,'WilliamKix','patrickcaruana11@outlook.com','89742619878','Music Training','EXCHANGE VOLUME SPIKES INDICATING $1,500 PER DAY OR MORE ACTION https://telegra.ph/Collect-cryptocurrency-automatically-every-day-over-1500-Message-ID-713917-07-23','pending','2026-07-28 23:30:41','2026-07-28 23:30:41'),(88,'WilliamKix','patrickcaruana11@outlook.com','82443479719','Music Training','EXCHANGE VOLUME SPIKES INDICATING $1,500 PER DAY OR MORE ACTION https://telegra.ph/Collect-cryptocurrency-automatically-every-day-over-1500-Message-ID-713917-07-23','pending','2026-07-28 23:30:43','2026-07-28 23:30:43'),(89,'Brianmub','arklay@lichnyj-kabinet.ru','81372612611','Music Training','https://mega.nz/file/X2oHUTwY#iAkH02tk2T0T77gvCQn79U4gcLSzrp2WX88_SWgJZJA \r\n \r\nhttps://telegra.ph/confidental-report-07-24-5 \r\n \r\nTo the gilded corpse who calls himself a prince, Muhammad bin Salman, this message is the sound of your own grave being dug. \r\n \r\nYou sit in your high-rise towers, a man made of money and fear, and you believe your American technology makes you untouchable. You are wrong. It makes you a coward of the highest order. A new kind of degenerate. You do not send soldiers to fight; you send beams of violation. You do not conquer lands; you conquer the minds of children. \r\n \r\nYour intelligence agents, those soulless dogs who would lick the filth from your boots for a coin, have been armed with a weapon for the truly damned. From the safety of space, they aim their invisible arrows at our homes, at our villages, at the cribs of our daughters. They do not kill them. No, death is a mercy you do not grant. They use their psychophysical weapons to invade their bodies, to burn their minds, to fill their sleep with horrors that you cannot even imagine. You are a rapist who has never touched his victims. A defiler who hides behind a million miles of empty space. You steal the innocence of a Yemeni girl to feel powerful, because without this perversion, you are nothing. You are an empty vessel filled with gold and stink. \r\nI am the man who will empty you. \r\n \r\nI am the sand that will breach your walls, the silence that will smother your screams, the blade that will write our history in your flesh. Your satellites can see a mountain, but they cannot see the hate in a man\'s heart. Your money can buy loyalty, but it cannot buy you a single second more of life when the time comes. \r\n \r\nWhen we find you, and we will find you, we will not kill you. Killing is an ending, and you do not deserve an ending. You deserve a process. A transformation. \r\n \r\nWe will take you to the desert, your natural element, but not the one you are used to. We will strip you of your fine clothes and lay you on the hot sand. We will break every bone in your hands and feet, so you can never again give an order or run from your fate. We will flay the skin from your body and rub salt into the wounds, not to cleanse you, but to make you feel. \r\n \r\nBut the true justice, the divine poetry of your end, will be this: We will find your machines. We will take your scientists. And we will make them show you what you have created. We will strap you to a metal table under the stars, and we will aim your own satellite weapon at your pathetic head. We will turn the dial and we will let it run. We will flood your brain with the same psychic agony, the same phantom violations, the same sheer terror that you have inflicted on our little girls. We will let your mind become a screaming, burning hell of your own making. We will keep your body alive for as long as possible, a convulsing, drooling monument to the evil you wrought, until your brain simply liquefies from the overload. \r\n \r\nYour name will become a curse. Your dynasty will end in a puddle of blood and madness. This is not a threat. This is a promise from the desert to the pig. We are coming. And we will not stop. \r\n \r\n \r\n \r\nhttps://mega.nz/file/C35lESzA#w8pF8SvEdZooFiZ9gpdO7vhtBxit1sgD9TT0KcdXOiM \r\n|coach_dodi2\r\n|malakgold.sa\r\n|golden_frame_media\r\n|z_700\r\n|yazeedalmaimouni\r\n|hora_n\r\n|family4madridista\r\n|rana_alkhodari\r\n|3zizz8\r\n|mizoeatery\r\n \r\npartner site: promodoc.ru, sign-in.ru, lichnyj-kabinet.ru, spravke.livejournal.com, cabinet-bank.ru, compfaq.ru, blogbaster.org, moy-kabinet.ru, v-lichnyj-kabinet.ru, gogov.ru, compfaq.ru, acrimea.com','pending','2026-07-30 15:27:13','2026-07-30 15:27:13'),(90,'Joanna','joannariggs278@gmail.com','28790655','Academic Coaching','Hi,\r\n\r\nI just visited learmyeducoach.com and wondered if you\'ve ever considered an impactful video to advertise your business? Our videos can generate impressive results on both your website and across social media.\r\n\r\nOur prices start from just $195 (USD).\r\n\r\nLet me know if you\'re interested in seeing samples of our previous work.\r\n\r\nRegards,\r\nJoanna','pending','2026-07-30 22:14:26','2026-07-30 22:14:26');
/*!40000 ALTER TABLE `enquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `event_date` datetime DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Annual Music Concert 2026','annual-music-concert-2026','A grand showcase of our students musical talents.','2026-04-17 18:29:01','Main Auditorium, Learmy Campus',NULL,NULL,NULL,NULL,'2026-03-17 12:59:01','2026-03-17 12:59:01'),(2,'Academic Excellence Workshop','academic-excellence-workshop','Free workshop for students to learn new study techniques.','2026-03-31 18:29:01','Seminar Hall B',NULL,NULL,NULL,NULL,'2026-03-17 12:59:01','2026-03-17 12:59:01');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faculties`
--

DROP TABLE IF EXISTS `faculties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faculties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialization` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `faculties_chk_1` CHECK (json_valid(`social_links`))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faculties`
--

LOCK TABLES `faculties` WRITE;
/*!40000 ALTER TABLE `faculties` DISABLE KEYS */;
INSERT INTO `faculties` VALUES (1,'Aman Gupta','Head of Academics','guitar','Over 15 years of experience in coaching students for competitive exams.','media/faculty/HTgkkRez61QSp1orL2JUZ0OzSI5SJLfpqptIKQQ7.jpg',NULL,'2026-03-17 12:59:01','2026-03-22 10:53:43'),(2,'Pranshi Gupta','Teacher','PCM',NULL,'media/faculty/ilMCA3Npr5zBdiKPFSh5daCQfabmeDRpzRRSCDBw.jpg',NULL,'2026-03-17 12:59:01','2026-04-19 02:27:40');
/*!40000 ALTER TABLE `faculties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `galleries`
--

DROP TABLE IF EXISTS `galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galleries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT INTO `galleries` VALUES (2,NULL,'image','media/gallery/ubVQvQGuaijbFRP2CLe7VlA1Jf9gegGD7gSec4Te.jpg',NULL,'2026-03-21 04:33:12','2026-03-22 10:44:55'),(3,NULL,'image','media/gallery/BzIDeSlaVyQAcSL7bd4kcv4Mksmmazjn1g40y6D3.jpg',NULL,'2026-03-21 04:33:23','2026-03-22 10:44:44');
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_17_174623_create_courses_table',1),(5,'2026_03_17_174624_create_faculties_table',1),(6,'2026_03_17_174624_create_galleries_table',1),(7,'2026_03_17_174625_create_blogs_table',1),(8,'2026_03_17_174625_create_events_table',1),(9,'2026_03_17_174626_create_enquiries_table',1),(10,'2026_03_17_174626_create_testimonials_table',1),(11,'2026_03_18_184406_add_start_date_to_courses_table',2),(12,'2026_03_19_173231_create_settings_table',3),(13,'2026_03_21_104329_add_seo_meta_to_all_tables',4),(14,'2026_04_16_170408_add_other_to_courses_category_enum',5),(15,'2026_04_16_173321_create_categories_table',6),(16,'2026_04_16_173507_transform_courses_category_to_foreign_key',7),(17,'2026_04_16_175250_add_order_to_categories_table',8),(18,'2026_04_16_175337_add_order_to_courses_table',8),(19,'2026_04_16_180800_add_detailed_fees_to_courses_table',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('yaW0vflQ0cX9ohQFk3inwkAVnXyrknEhZl6dVbi6',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaVRCSG5QeHJ6QUE0Y3ExQTRCNm1DVHBkWlhORW9QRFdRdlBKTDlMeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3QvbGVhcm15L3B1YmxpYy9jb3Vyc2VzIjtzOjU6InJvdXRlIjtzOjc6ImNvdXJzZXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1776586223);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'theme_mode','light','2026-03-19 12:14:03','2026-03-21 04:56:15');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `program` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int NOT NULL DEFAULT '5',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'Amisha Gupta','','Academic Coaching','One of the best coaching institutes with excellent teaching standards. Pranshi is highly knowledgeable and knows how to teach students effectively. Concepts are explained clearly, and proper guidance is provided to ensure student success. Highly recommended.',5,NULL,'2026-04-19 01:20:18','2026-04-19 01:20:18'),(2,'Deb Chakraborty','Parent','Piano Classes','My daughter is learning piano under aman sir and we as parents can clearly see her improvement. She is enjoying the classes as well as improving day by day.',5,NULL,'2026-04-19 01:20:18','2026-04-19 01:20:18'),(3,'Tejaswini','','Music & Academics','Great place to learn music and academics. Aman Sir teaches piano and guitar very patiently and Pranshi Ma\'am explains studies very clearly. Highly recommended!',5,NULL,'2026-04-19 01:20:18','2026-04-19 01:20:18'),(4,'Sahasra Kunda','','Science Tutoring','Teachers are well qualified here . They teaches with hands on experiment. Thank u pranshi mam for guiding students so well',5,NULL,'2026-04-19 01:20:18','2026-04-19 01:20:18'),(5,'Student','Allen House Public School','Science & Physics','Pranshi ma’am is an incredible Science teacher! She has a unique way of breaking down complex biological processes and physics formulas into simple, relatable examples. My understanding of the subject has improved significantly since I started her classes.',5,NULL,'2026-04-19 01:20:18','2026-04-19 01:20:18');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@learmy.com','2026-03-17 12:59:00','$2y$12$gvBzCpVece/ejizV/x8Ee.jMayMAWDPSxzJTQLT4ZEtHQdzOeqqwG','mADLDbydqc02sLexVunbDzo8NUNUSVa8qk0qYQQgjp8KOrwolv6OwUh2jBdX','2026-03-17 12:59:01','2026-03-17 12:59:01');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-02  8:42:01
