-- Seed data aligned with bootstrap/img Ethiopian covers
SET NAMES utf8mb4;
USE `www_project`;

INSERT INTO `admin` (`name`, `pass`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- password: password

INSERT INTO `publisher` (`publisherid`, `publisher_name`) VALUES
(1, 'አዲስ አበባ ዩኒቨርሲቲ ፕሬስ'),
(2, 'መጽሐፍት አትሚ'),
(3, 'ሸገር ፕብሊሽንግ'),
(4, 'ክብረት ቡክስ');

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'ልብ ወለድ', 'fiction'),
(2, 'ታሪክ', 'history'),
(3, 'ግጥም', 'poetry'),
(4, 'ሃይማኖት', 'religion');

INSERT INTO `books` (`book_isbn`, `book_title`, `book_author`, `book_image`, `book_descr`, `book_price`, `publisherid`, `category_id`, `language`) VALUES
('978-et-0001', 'ደርቶ ገዳ', 'በዓሉ ግርማ', 'dertogada.jpg', 'ታዋቂ የኢትዮጵያ ልብ ወለድ።', 250.00, 2, 1, 'am'),
('978-et-0002', 'እመጓ', 'በዓሉ ግርማ', 'emegua.jpg', 'የእመጓ ታሪክ።', 220.00, 2, 1, 'am'),
('978-et-0003', 'ሰመመን', 'ሲሳይ ንጉሴ', 'sememen.jpg', 'የሰመመን ልብ ወለድ።', 200.00, 3, 1, 'am'),
('978-et-0004', 'አሌካ', 'ተስፋዬ ገብረአብ', 'aleka.jpg', 'አሌካ።', 180.00, 1, 2, 'am'),
('978-et-0005', 'አልፈራም', 'አብዱልከሪም አህመድ', 'alferam.jpg', 'አልፈራም።', 190.00, 3, 1, 'am'),
('978-et-0006', 'አሜን', 'ምህረት ደበበ', 'amen.jpg', 'አሜን።', 210.00, 4, 4, 'am'),
('978-et-0007', 'አንድሮሜዳ', 'ዮናስ ገብረመድህን', 'andromeda.jpg', 'አንድሮሜዳ።', 230.00, 2, 1, 'am'),
('978-et-0008', 'ፍቅር እስከ መቃብር', 'ሀዲስ አለማየሁ', 'fkr.jpg', 'ፍቅር እስከ መቃብር።', 280.00, 1, 1, 'am'),
('978-et-0009', 'ከበላ', 'አዳም ረታ', 'kbela.jpg', 'ከበላ።', 175.00, 3, 3, 'am'),
('978-et-0010', 'ክታት', 'በቀለ ገብረማርያም', 'ktat.jpg', 'ክታት።', 165.00, 4, 2, 'am'),
('978-et-0011', 'ኩልፍ', 'ተሾመ ገብረ', 'kulf.jpg', 'ኩልፍ።', 195.00, 2, 1, 'am'),
('978-et-0012', 'ልላሰው', 'አስረስ መኮንን', 'lelasew.jpg', 'ልላሰው።', 185.00, 3, 1, 'am'),
('978-et-0013', 'መሎስ', 'ዳዊት ወንድይራድ', 'melos.jpg', 'መሎስ።', 205.00, 1, 3, 'am'),
('978-et-0014', 'ተከርጨም', 'ገብረሂወት ገብረእግዚአብሔር', 'tekerchem.jpg', 'ተከርጨም።', 170.00, 4, 2, 'am'),
('978-et-0015', 'ዮቶድ', 'ማሙሸ ወርቁ', 'yotod.jpg', 'ዮቶድ።', 160.00, 2, 1, 'am'),
('978-et-0016', 'ዘምራ', 'ኤልሳቤጥ ወልደጊዮርጊስ', 'zamra.jpg', 'ዘምራ።', 215.00, 3, 3, 'am'),
('978-et-0017', 'ዝጎራ', 'አበበ ገላው', 'zgora.jpg', 'ዝጎራ።', 225.00, 1, 1, 'am'),
('978-et-0018', 'ዙበይዳ', 'ፋጢማ አሊ', 'zubeyda.jpg', 'ዙበይዳ።', 240.00, 4, 1, 'am'),
('978-et-0019', 'ብርቅ', 'ተፈሪ ወልደስላሴ', 'brk.jpg', 'ብርቅ።', 155.00, 2, 2, 'am'),
('978-et-0020', '666', 'ዮሐንስ አበራ', '666.jpg', '666።', 260.00, 3, 1, 'am');
