-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2026 at 01:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ebook_store_ethiopia`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `pdf_file` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `page_count` int(11) DEFAULT NULL,
  `language` enum('English','Amharic','Afan Oromo','Tigrigna','Somali','Other') DEFAULT 'English',
  `country` enum('Ethiopia','International') DEFAULT 'Ethiopia',
  `publisher` varchar(100) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `publication_city` varchar(100) DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `award_winning` tinyint(1) DEFAULT 0,
  `bestseller` tinyint(1) DEFAULT 0,
  `reading_level` varchar(50) DEFAULT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `format` varchar(20) DEFAULT 'PDF',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `description`, `price`, `category_id`, `cover_image`, `pdf_file`, `is_featured`, `page_count`, `language`, `country`, `publisher`, `isbn`, `publication_city`, `publication_date`, `award_winning`, `bestseller`, `reading_level`, `file_size`, `format`, `created_at`, `updated_at`) VALUES
(40, 'Fikir Eske Mekabir', 'Haddis Alemayehu', 'The greatest Amharic novel ever written. A masterpiece of Ethiopian literature.', 34.99, 27, NULL, NULL, 1, 850, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 10:52:37', '2026-02-14 12:02:13'),
(41, 'Dertogada', 'Yismake Worku', 'A modern classic of Ethiopian literature, blending poetry and prose.', 29.99, 27, NULL, NULL, 1, 450, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 10:52:37', '2026-02-14 12:02:13'),
(42, 'Oromai', 'Bealu Girma', 'A powerful political novel about Ethiopian society.', 32.99, 31, NULL, NULL, 1, 550, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 10:52:37', '2026-02-14 12:02:13'),
(43, 'The Shadow King', 'Maaza Mengiste', 'Shortlisted for the Booker Prize. A powerful novel about Ethiopian women soldiers.', 29.99, 29, NULL, NULL, 1, 430, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 10:52:37', '2026-02-14 12:02:13'),
(44, 'Ye Ethiopia Tarik', 'Aleqa Asres Yenesew', 'Comprehensive history of Ethiopia in Amharic.', 38.99, 29, NULL, 'Ye_Ethiopia_Tarik.pdf', 1, 750, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, '1024', 'PDF', '2026-02-13 10:52:37', '2026-02-14 12:03:36'),
(45, 'Beneath the Lion\'s Gaze', 'Maaza Mengiste', 'A novel set during the Ethiopian Revolution.', 26.99, 28, NULL, NULL, 1, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 10:52:37', '2026-02-14 12:02:13'),
(46, 'Fikir Eske Mekabir', 'Haddis Alemayehu', 'የኢትዮጵያ ልቦለድ አባት እንደሚባሉት ሀዲስ አለማየሁ የፃፉት ዘመን ተሻጋሪ ልቦለድ። በኢትዮጵያ ማኅበረሰብ ውስጥ ያለውን ፍቅር፣ ባህልና ወግ በሚገርም ሁኔታ የሚያሳይ ነው።', 34.99, 27, NULL, NULL, 1, 850, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(47, 'Dertogada', 'Yismake Worku', 'ዘመናዊ የአማርኛ ሥነ-ጽሑፍን በማደስ የታወቁት ይስማከ ወርቁ የፃፉት ልቦለድ። የፍቅር፣ የተስፋና የትግል ታሪክን በግጥማዊ አጻጻፍ ያሳያል።', 29.99, 27, NULL, NULL, 1, 450, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(48, 'Oromai', 'Bealu Girma', 'በታዋቂው ጋዜጠኛና ደራሲ በአሉ ግርማ የተፃፈ ልቦለድ። መጽሐፉ በኢትዮጵያ ማኅበረሰብና ፖለቲካ ውስጥ ያለውን እውነታ ያሳያል።', 32.99, 27, NULL, NULL, 1, 550, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(49, 'Tizita', 'Haddis Alemayehu', 'ሀዲስ አለማየሁ ከፍቅር እስከ መቃብር በኋላ የፃፉት ሁለተኛው ልቦለድ። በዚህ መጽሐፍ ደራሲው የኢትዮጵያን ባህልና ወግ አሳይቷል።', 28.99, 27, NULL, NULL, 1, 480, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(50, 'Araya', 'Mammo Wudneh', 'የኢትዮጵያ ልቦለድ ዘውግ አዲስ ምዕራፍ የከፈተ የመሞ ውድነህ ድንቅ ሥራ።', 31.99, 27, NULL, NULL, 0, 520, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(51, 'Ye-Imba Debter', 'Haddis Alemayehu', 'የሀዲስ አለማየሁ ሦስተኛው ልቦለድ። በዚህ መጽሐፍ ደራሲው የኢትዮጵያን ገጠር ማኅበረሰብ አሳይቷል።', 27.99, 27, NULL, NULL, 0, 380, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(52, 'Ye Sheger Betoch', 'Sebhat Gebre-Egziabher', 'የአዲስ አበባ ከተማን ማኅበረሰብ፣ ባህልና ኑሮ በሚገርም ቀልዳዊ አጻጻፍ ያሳያል።', 26.99, 27, NULL, NULL, 1, 420, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(53, 'Kadmas Bashager', 'Sisay Nigusu', 'ዘመናዊ የአማርኛ ልቦለድ በታዋቂው ደራሲ ሲሳይ ንጉሱ የተፃፈ።', 24.99, 27, NULL, NULL, 0, 350, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(54, 'The Shadow King', 'Maaza Mengiste', 'Shortlisted for the Booker Prize 2020. A powerful novel reimagining Ethiopia\'s resistance against Italian fascism through the eyes of women soldiers.', 29.99, 28, NULL, NULL, 1, 430, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(55, 'Beneath the Lion\'s Gaze', 'Maaza Mengiste', 'A debut novel set during the Ethiopian Revolution of 1974. Follows one family\'s struggle to survive as their world collapses.', 26.99, 28, NULL, NULL, 1, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(56, 'The Beautiful Things That Heaven Bears', 'Dinaw Mengestu', 'A luminous debut novel about an Ethiopian immigrant in Washington D.C. Winner of the Guardian First Book Award.', 24.99, 28, NULL, NULL, 1, 240, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(57, 'Notes from the Hyena\'s Belly', 'Nega Mezlekia', 'An unforgettable memoir of growing up in Ethiopia during the turbulent 1960s and 1970s. Winner of the Governor General\'s Award.', 22.99, 28, NULL, NULL, 1, 380, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(58, 'Shinega\'s Village', 'Sahle Sellassie', 'One of the first modern Ethiopian novels written in English. Provides unique insights into rural Ethiopian life.', 25.99, 28, NULL, NULL, 0, 280, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(59, 'The Thirteenth Sun', 'Daniachew Worku', 'A modernist masterpiece published in the Heinemann African Writers Series. Explores the clash between tradition and modernity.', 26.99, 28, NULL, NULL, 0, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(60, 'Oda Oak Oracle', 'Tsegaye Gabre-Medhin', 'A poetic drama by Ethiopia\'s Poet Laureate, exploring traditional Oromo religious practices.', 24.99, 28, NULL, NULL, 0, 120, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:30', '2026-02-14 12:02:13'),
(61, 'Ye Ethiopia Tarik: Atse Tewodros', 'Aleqa Asres Yenesew', 'የኢትዮጵያ ታሪክ ተመራማሪ አለቃ አስረስ የነሴው ድንቅ ሥራ። የዳግማዊ ቴዎድሮስን ዘመን በዝርዝር ያሳያል።', 38.99, 29, NULL, 'Ye_Ethiopia_Tarik.pdf', 1, 750, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, '1024000', 'PDF', '2026-02-13 11:07:31', '2026-02-14 11:45:36'),
(62, 'Afran Qallo', 'Dr. Mohammed Hassen', 'A groundbreaking study of the Oromo people and their traditional Gadaa system. Essential for understanding Ethiopian cultural diversity.', 44.99, 29, NULL, NULL, 1, 680, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(63, 'The Emperor: Downfall of an Autocrat', 'Ryszard Kapuściński', 'A classic work depicting the final years of Emperor Haile Selassie\'s reign. One of the most widely read books about Ethiopia.', 27.99, 29, NULL, NULL, 1, 180, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(64, 'The History of the Galla', 'Bahrey', 'A classic 16th-century historical text about the Oromo people, originally written in Ge\'ez.', 42.99, 29, NULL, NULL, 0, 200, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(65, 'A History of Modern Ethiopia', 'Bahru Zewde', 'The most comprehensive history of Ethiopia from 1855 to the present by a renowned Ethiopian historian.', 49.99, 29, NULL, NULL, 1, 350, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(66, 'The Battle of Adwa', 'Raymond Jonas', 'The definitive account of the Battle of Adwa where Ethiopia defeated Italy and maintained its independence.', 32.99, 29, NULL, NULL, 1, 420, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(67, 'Oromo Wisdom', 'Dr. Mohammed Hassen', 'A collection of Oromo proverbs, folktales, and philosophical sayings with cultural commentary.', 39.99, 30, NULL, NULL, 1, 600, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(68, 'Ethiopian Orthodox Tewahedo Church', 'Dr. Getachew Haile', 'A comprehensive guide to the history, traditions, and practices of the Ethiopian Orthodox Church.', 45.99, 30, NULL, NULL, 1, 550, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(69, 'Ethiopian Cuisine', 'Yohanis Gebreyesus', 'A beautiful cookbook featuring traditional Ethiopian recipes with stunning photography.', 35.99, 30, NULL, NULL, 1, 280, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(70, 'Coffee Ceremony', 'Tsegaye Berhe', 'Explore the rich tradition of the Ethiopian coffee ceremony, its history and cultural significance.', 29.99, 30, NULL, NULL, 0, 200, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(71, 'Ethiopian Traditional Music', 'Ashenafi Kebede', 'A scholarly work on the diverse musical traditions of Ethiopia\'s ethnic groups.', 34.99, 30, NULL, NULL, 0, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(72, 'Ye-Key Kokeb Tiri', 'Bealu Girma', 'በአሉ ግርማ የተፃፈ ሁለተኛው ልቦለድ። በኢትዮጵያ ፖለቲካ ውስጥ ያለውን ስልጣን ጉጉትና ሙስና ያሳያል።', 30.99, 31, NULL, NULL, 1, 420, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(73, 'Understanding Contemporary Ethiopia', 'Gérard Prunier', 'A collection of essays by leading scholars on modern Ethiopian politics and society.', 38.99, 31, NULL, NULL, 1, 550, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(74, 'The Federal Experiment', 'Dr. Merera Gudina', 'An analysis of Ethiopia\'s ethnic federalism system by a prominent political scientist.', 36.99, 31, NULL, NULL, 0, 380, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(75, 'Ethiopia: The Last Two Frontiers', 'John Markakis', 'A comprehensive study of Ethiopia\'s political development and challenges.', 42.99, 31, NULL, NULL, 0, 450, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(76, 'Democracy and Development in Ethiopia', 'Dr. Kassahun Berhanu', 'An examination of the relationship between political reform and economic development in Ethiopia.', 33.99, 31, NULL, NULL, 0, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(77, 'The Lion of Judah', 'Elizabeth Laird', 'A beautifully illustrated children\'s book about the Ethiopian flag and its symbolism.', 18.99, 32, NULL, NULL, 1, 32, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(78, 'ABCs of Ethiopia', 'Jane Kurtz', 'Learn the alphabet through Ethiopian culture, animals, and traditions. Colorful illustrations.', 16.99, 32, NULL, NULL, 1, 28, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(79, 'Saba: Under the Hyena\'s Foot', 'Jane Kurtz', 'An adventure story set in 19th century Ethiopia for young readers.', 14.99, 32, NULL, NULL, 1, 180, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(80, 'The Story of Lalibela', 'Getnet Demeke', 'The fascinating story of how the rock-hewn churches of Lalibela were built, told for children.', 15.99, 32, NULL, NULL, 0, 40, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(81, 'Ethiopian Animal Tales', 'Mesfin Habte', 'A collection of traditional Ethiopian folktales featuring clever animals and important lessons.', 17.99, 32, NULL, NULL, 1, 64, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(82, 'Selam\'s First Day', 'Meron Hadera', 'A heartwarming story about a little girl\'s first day at school in Addis Ababa.', 13.99, 32, NULL, NULL, 0, 24, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(83, 'Ye Fikir Tizita', 'Lemma Demissew', 'የታዋቂው ገጣሚ ለማ ደምሰው የግጥም ስብስብ። በኢትዮጵያ ትምህርት ቤቶች በስፋት የሚማረው።', 18.99, 33, NULL, NULL, 0, 150, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(84, 'Songs We Learn from Trees', 'Chris Beckett', 'A poetry collection that explores Ethiopian-British identity and family history.', 22.99, 33, NULL, NULL, 0, 120, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(85, 'Ethiopian Love Poems', 'Various', 'A beautiful collection of traditional and modern love poetry from Ethiopia.', 19.99, 33, NULL, NULL, 1, 180, '', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(86, 'The Collected Poems of Tsegaye Gabre-Medhin', 'Tsegaye Gabre-Medhin', 'The complete poetic works of Ethiopia\'s Poet Laureate.', 42.99, 33, NULL, NULL, 0, 550, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(87, 'Ye Ethiopia Kine', 'Kebede Michael', 'የኢትዮጵያ ባህላዊ ቅኔዎች ስብስብ በታዋቂው ምሁር ከበደ ሚካኤል።', 24.99, 33, NULL, NULL, 0, 280, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(88, 'King of Kings: The Life of Haile Selassie', 'Asfa-Wossen Asserate', 'A definitive biography of Emperor Haile Selassie by his great-nephew.', 36.99, 34, NULL, NULL, 1, 380, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(89, 'Tewodros: The Lion of Ethiopia', 'Paul B. Henze', 'The dramatic story of Emperor Tewodros II, one of Ethiopia\'s most fascinating rulers.', 32.99, 34, NULL, NULL, 1, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(90, 'The Life and Times of Menelik II', 'Harold G. Marcus', 'A scholarly biography of the emperor who defeated Italy at Adwa and modernized Ethiopia.', 34.99, 34, NULL, NULL, 0, 350, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(91, 'Abune Petros: A Biography', 'Dr. Sergew Hable Selassie', 'The inspiring story of the Ethiopian bishop and martyr who stood against Italian occupation.', 28.99, 34, NULL, NULL, 0, 220, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(92, 'Haddis Alemayehu: A Literary Giant', 'Fikre Tolossa', 'A biography of Ethiopia\'s greatest novelist, author of Fikir Eske Mekabir.', 29.99, 34, NULL, NULL, 0, 280, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(93, 'Python Programming: From Zero to Hero', 'Abraham Tekle', 'Learn Python programming from scratch with practical Ethiopian examples and projects. Includes exercises on building real applications.', 45.99, 35, NULL, NULL, 1, 620, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(94, 'JavaScript: The Complete Guide', 'Michael Johnson', 'Master JavaScript from basics to advanced concepts like closures, promises, and async programming. Includes ES6+ features.', 49.99, 35, NULL, NULL, 1, 780, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(95, 'PHP for Web Development', 'David Williams', 'Learn PHP programming with MySQL. Build dynamic websites and web applications. Includes e-commerce project.', 44.99, 35, NULL, NULL, 1, 550, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(96, 'Java Programming Masterclass', 'Robert Chen', 'Comprehensive Java course covering OOP, data structures, algorithms, and design patterns. Perfect for beginners.', 54.99, 35, NULL, NULL, 0, 890, 'English', 'International', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(97, 'C++ for Beginners', 'Sarah Gebre', 'Learn C++ programming with easy-to-follow examples and exercises. Covers pointers, memory management, and STL.', 42.99, 35, NULL, NULL, 0, 480, 'English', 'International', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(98, 'SQL Database Design', 'Daniel Messele', 'Master SQL and database design. Learn to create efficient, scalable databases for your applications.', 38.99, 35, NULL, NULL, 1, 390, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(99, 'Git & GitHub Essentials', 'Thomas Lemma', 'Version control with Git and collaboration on GitHub. Essential for every developer.', 29.99, 35, NULL, NULL, 0, 220, 'English', 'International', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(100, 'Clean Code: Best Practices', 'Helen Tsegaye', 'Write clean, maintainable, and efficient code. Learn design patterns and refactoring techniques.', 41.99, 35, NULL, NULL, 0, 350, 'English', 'International', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(101, 'Data Structures & Algorithms', 'Yonas Desta', 'Master essential data structures and algorithms for technical interviews. In Java and Python.', 52.99, 35, NULL, NULL, 1, 720, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:07:31', '2026-02-14 12:02:13'),
(102, 'Fikir Eske Mekabir', 'Haddis Alemayehu', 'የኢትዮጵያ ልቦለድ አባት...', 34.99, 27, NULL, NULL, 1, 850, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(103, 'Dertogada', 'Yismake Worku', 'ዘመናዊ የአማርኛ ሥነ-ጽሑፍ...', 29.99, 27, NULL, NULL, 1, 450, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(104, 'Oromai', 'Bealu Girma', 'በታዋቂው ጋዜጠኛና ደራሲ...', 32.99, 27, NULL, NULL, 1, 550, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(105, 'Tizita', 'Haddis Alemayehu', 'ሀዲስ አለማየሁ ከፍቅር...', 28.99, 27, NULL, NULL, 0, 480, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(106, 'Araya', 'Mammo Wudneh', 'የኢትዮጵያ ልቦለድ ዘውግ...', 31.99, 27, NULL, NULL, 0, 520, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(107, 'Ye-Imba Debter', 'Haddis Alemayehu', 'የሀዲስ አለማየሁ ሦስተኛው...', 27.99, 27, NULL, NULL, 0, 380, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(108, 'Ye Sheger Betoch', 'Sebhat Gebre-Egziabher', 'የአዲስ አበባ ከተማ...', 26.99, 27, NULL, NULL, 1, 420, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(109, 'Kadmas Bashager', 'Sisay Nigusu', 'ዘመናዊ የአማርኛ ልቦለድ...', 24.99, 27, NULL, NULL, 0, 350, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(110, 'The Shadow King', 'Maaza Mengiste', 'Shortlisted for the Booker Prize 2020...', 29.99, 28, NULL, NULL, 1, 430, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(111, 'Beneath the Lion\'s Gaze', 'Maaza Mengiste', 'A debut novel set during the Ethiopian Revolution...', 26.99, 28, NULL, NULL, 1, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(112, 'The Beautiful Things That Heaven Bears', 'Dinaw Mengestu', 'A luminous debut novel about an Ethiopian immigrant...', 24.99, 28, NULL, NULL, 1, 240, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(113, 'Notes from the Hyena\'s Belly', 'Nega Mezlekia', 'An unforgettable memoir of growing up in Ethiopia...', 22.99, 28, NULL, NULL, 1, 380, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(114, 'Shinega\'s Village', 'Sahle Sellassie', 'One of the first modern Ethiopian novels written in English...', 25.99, 28, NULL, NULL, 0, 280, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(115, 'The Thirteenth Sun', 'Daniachew Worku', 'A modernist masterpiece published in the Heinemann African Writers Series...', 26.99, 28, NULL, NULL, 0, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(116, 'Oda Oak Oracle', 'Tsegaye Gabre-Medhin', 'A poetic drama exploring traditional Oromo religious practices...', 24.99, 28, NULL, NULL, 0, 120, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:55', '2026-02-14 12:02:13'),
(117, 'Ye Ethiopia Tarik: Atse Tewodros', 'Aleqa Asres Yenesew', 'Historical account...', 38.99, 29, NULL, 'Ye_Ethiopia_Tarik.pdf', 1, 750, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, '1024000', 'PDF', '2026-02-13 11:12:56', '2026-02-14 11:45:36'),
(118, 'Afran Qallo', 'Dr. Mohammed Hassen', 'Study of Oromo people and Gadaa system...', 44.99, 29, NULL, NULL, 1, 680, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(119, 'The Emperor: Downfall of an Autocrat', 'Ryszard Kapuściński', 'Depicting final years of Haile Selassie...', 27.99, 29, NULL, NULL, 1, 180, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(120, 'The History of the Galla', 'Bahrey', '16th-century historical text...', 42.99, 29, NULL, NULL, 0, 200, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(121, 'A History of Modern Ethiopia', 'Bahru Zewde', 'Comprehensive history from 1855 to present...', 49.99, 29, NULL, NULL, 1, 350, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(122, 'The Battle of Adwa', 'Raymond Jonas', 'Account of the Battle of Adwa...', 32.99, 29, NULL, NULL, 1, 420, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(123, 'Oromo Wisdom', 'Dr. Mohammed Hassen', 'Collection of Oromo proverbs...', 39.99, 30, NULL, NULL, 1, 600, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(124, 'Ethiopian Orthodox Tewahedo Church', 'Dr. Getachew Haile', 'Guide to the Ethiopian Orthodox Church...', 45.99, 30, NULL, NULL, 1, 550, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(125, 'Ethiopian Cuisine', 'Yohanis Gebreyesus', 'Traditional Ethiopian recipes...', 35.99, 30, NULL, NULL, 1, 280, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(126, 'Coffee Ceremony', 'Tsegaye Berhe', 'Rich tradition of Ethiopian coffee ceremony...', 29.99, 30, NULL, NULL, 0, 200, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(127, 'Ethiopian Traditional Music', 'Ashenafi Kebede', 'Scholarly work on Ethiopian musical traditions...', 34.99, 30, NULL, NULL, 0, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(128, 'Ye-Key Kokeb Tiri', 'Bealu Girma', 'Political novel...', 30.99, 31, NULL, NULL, 1, 420, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(129, 'Understanding Contemporary Ethiopia', 'Gérard Prunier', 'Essays on modern Ethiopian politics...', 38.99, 31, NULL, NULL, 1, 550, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(130, 'The Federal Experiment', 'Dr. Merera Gudina', 'Analysis of ethnic federalism...', 36.99, 31, NULL, NULL, 0, 380, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(131, 'Ethiopia: The Last Two Frontiers', 'John Markakis', 'Study of political development...', 42.99, 31, NULL, NULL, 0, 450, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(132, 'Democracy and Development in Ethiopia', 'Dr. Kassahun Berhanu', 'Political reform and economic development...', 33.99, 31, NULL, NULL, 0, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(133, 'The Lion of Judah', 'Elizabeth Laird', 'Illustrated children\'s book...', 18.99, 32, NULL, NULL, 1, 32, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(134, 'ABCs of Ethiopia', 'Jane Kurtz', 'Learn alphabet through Ethiopian culture...', 16.99, 32, NULL, NULL, 1, 28, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(135, 'Saba: Under the Hyena\'s Foot', 'Jane Kurtz', 'Adventure story in 19th century...', 14.99, 32, NULL, NULL, 1, 180, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(136, 'The Story of Lalibela', 'Getnet Demeke', 'Story of rock-hewn churches...', 15.99, 32, NULL, NULL, 0, 40, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(137, 'Ethiopian Animal Tales', 'Mesfin Habte', 'Traditional Ethiopian folktales...', 17.99, 32, NULL, NULL, 1, 64, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(138, 'Selam\'s First Day', 'Meron Hadera', 'Little girl\'s first day in Addis Ababa...', 13.99, 32, NULL, NULL, 0, 24, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(139, 'Ye Fikir Tizita', 'Lemma Demissew', 'የታዋቂው ገጣሚ ለማ ደምሰው...', 18.99, 33, NULL, NULL, 0, 150, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(140, 'Songs We Learn from Trees', 'Chris Beckett', 'Explores Ethiopian-British identity...', 22.99, 33, NULL, NULL, 0, 120, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(141, 'Ethiopian Love Poems', 'Various', 'Collection of traditional and modern love poetry...', 19.99, 33, NULL, NULL, 1, 180, '', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(142, 'The Collected Poems of Tsegaye Gabre-Medhin', 'Tsegaye Gabre-Medhin', 'Complete poetic works...', 42.99, 33, NULL, NULL, 0, 550, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(143, 'Ye Ethiopia Kine', 'Kebede Michael', 'የኢትዮጵያ ባህላዊ ቅኔዎች...', 24.99, 33, NULL, NULL, 0, 280, 'Amharic', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(144, 'King of Kings: The Life of Haile Selassie', 'Asfa-Wossen Asserate', 'Biography of Emperor Haile Selassie...', 36.99, 34, NULL, NULL, 1, 380, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(145, 'Tewodros: The Lion of Ethiopia', 'Paul B. Henze', 'Story of Emperor Tewodros II...', 32.99, 34, NULL, NULL, 1, 320, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(146, 'The Life and Times of Menelik II', 'Harold G. Marcus', 'Biography of Emperor Menelik II...', 34.99, 34, NULL, NULL, 0, 350, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(147, 'Abune Petros: A Biography', 'Dr. Sergew Hable Selassie', 'Story of Ethiopian bishop and martyr...', 28.99, 34, NULL, NULL, 0, 220, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(148, 'Haddis Alemayehu: A Literary Giant', 'Fikre Tolossa', 'Biography of Haddis Alemayehu...', 29.99, 34, NULL, NULL, 0, 280, 'English', 'Ethiopia', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(149, 'Python Programming: From Zero to Hero', 'Abraham Tekle', 'Learn Python...', 45.99, 35, NULL, NULL, 1, 620, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(150, 'JavaScript: The Complete Guide', 'Michael Johnson', 'Master JavaScript...', 49.99, 35, NULL, NULL, 1, 780, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(151, 'PHP for Web Development', 'David Williams', 'Learn PHP...', 44.99, 35, NULL, NULL, 1, 550, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(152, 'Java Programming Masterclass', 'Robert Chen', 'Comprehensive Java course...', 54.99, 35, NULL, NULL, 0, 890, 'English', 'International', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(153, 'C++ for Beginners', 'Sarah Gebre', 'Learn C++...', 42.99, 35, NULL, NULL, 0, 480, 'English', 'International', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(154, 'SQL Database Design', 'Daniel Messele', 'Master SQL...', 38.99, 35, NULL, NULL, 1, 390, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(155, 'Git & GitHub Essentials', 'Thomas Lemma', 'Version control with Git...', 29.99, 35, NULL, NULL, 0, 220, 'English', 'International', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(156, 'Clean Code: Best Practices', 'Helen Tsegaye', 'Write clean, maintainable code...', 41.99, 35, NULL, NULL, 0, 350, 'English', 'International', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(157, 'Data Structures & Algorithms', 'Yonas Desta', 'Master essential data structures...', 52.99, 35, NULL, NULL, 1, 720, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(158, 'Machine Learning Fundamentals', 'Dr. Solomon Ayele', 'Intro to ML...', 59.99, 36, NULL, 'Machine_Learning_Fundamentals.pdf', 1, 580, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, '1024', 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:03:36'),
(159, 'Deep Learning with Python', 'Bereket Hailu', 'Build neural networks...', 64.99, 36, NULL, 'Deep_Learning_with_Python_1771070063.pdf', 1, 690, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, '20866662', 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(160, 'Artificial Intelligence: A Modern Approach', 'Hanna Tesfaye', 'Comprehensive AI guide...', 72.99, 36, NULL, NULL, 1, 720, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(161, 'Hands-On AI Projects', 'Selam Tesfaye', 'Practical AI projects...', 68.99, 36, NULL, NULL, 1, 640, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(162, 'AI for Everyone', 'Daniel Gebre', 'Non-technical introduction...', 39.99, 36, NULL, NULL, 0, 320, 'English', 'International', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(163, 'Neural Networks in Practice', 'Mekdes Alemu', 'Hands-on neural networks...', 62.99, 36, NULL, NULL, 1, 580, 'English', 'International', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(164, 'Deep Reinforcement Learning', 'Yosef Tadesse', 'Explore RL algorithms...', 69.99, 36, NULL, NULL, 0, 500, 'English', 'International', NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(165, 'AI Ethics and Society', 'Hanna Tesfaye', 'AI ethics and social impact...', 55.99, 36, NULL, NULL, 0, 400, 'English', 'International', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'PDF', '2026-02-13 11:12:56', '2026-02-14 12:02:13'),
(167, 'Python Handwritten', '@codersworld', 'this note is very amazing and bigenners friendly and very simple to understand', 2.00, 39, '1771066903_69905617cbda6.jpg', 'Deep_Learning_with_Python_1771070063.pdf', 1, NULL, 'English', 'Ethiopia', 'coders', NULL, NULL, NULL, 1, 0, NULL, '20866662', 'PDF', '2026-02-14 11:01:43', '2026-02-14 12:03:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_author` (`author`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_bestseller` (`bestseller`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_country` (`country`),
  ADD KEY `idx_ethiopian_books` (`country`,`language`),
  ADD KEY `idx_ethiopian_featured` (`country`,`is_featured`),
  ADD KEY `idx_publisher` (`publisher`);
ALTER TABLE `books` ADD FULLTEXT KEY `ft_search` (`title`,`author`,`description`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
