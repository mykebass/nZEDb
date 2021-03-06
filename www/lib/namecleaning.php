<?php
require_once(WWW_DIR."/lib/groups.php");

	//
	//	Cleans names for collections/releases/imports/namefixer.
	//
	class nameCleaning
	{
		//
		//	Cleans usenet subject before inserting, used for collectionhash.
		//
		public function collectionsCleaner($subject, $type="normal")
		{
			//Parts/files
			$cleansubject = preg_replace('/((( \(\d\d\) -|(\d\d)? - \d\d\.|\d{4} \d\d -) | - \d\d-| \d\d\. [a-z]).+| \d\d of \d\d| \dof\d)\.mp3"?|(\(|\[|\s)\d{1,4}(\/|(\s|_)of(\s|_)|\-)\d{1,4}(\)|\]|\s|$|:)|\(\d{1,3}\|\d{1,3}\)|\-\d{1,3}\-\d{1,3}\.|\s\d{1,3}\sof\s\d{1,3}\.|\s\d{1,3}\/\d{1,3}|\d{1,3}of\d{1,3}\.|^\d{1,3}\/\d{1,3}\s|\d{1,3} - of \d{1,3}/i', ' ', $subject);
			//Anything between the quotes. Too much variance within the quotes, so remove it completely.
			$cleansubject = preg_replace('/\".+\"/i', ' ', $cleansubject);
			//File extensions - If it was not quotes.
			$cleansubject = preg_replace('/(-? [a-z0-9]+-?|\(?\d{4}\)?(_|-)[a-z0-9]+)\.jpg"?| [a-z0-9]+\.mu3"?|((\d{1,3})?\.part(\d{1,5})?|\d{1,5} ?|sample|- Partie \d+)?\.(7z|\d{3}(?=(\s|"))|avi|diz|docx?|epub|idx|iso|jpg|m3u|m4a|mds|mkv|mobi|mp4|nfo|nzb|par(\s?2|")|pdf|rar|rev|rtf|r\d\d|sfv|srs|srr|sub|txt|vol.+(par2)|xls|zip|z{2,3})"?|(\s|(\d{2,3})?\-)\d{2,3}\.mp3|\d{2,3}\.pdf|\.part\d{1,4}\./i', ' ', $cleansubject);
			//File Sizes - Non unique ones.
			$cleansubject = preg_replace('/\d{1,3}(,|\.|\/)\d{1,3}\s(k|m|g)b|(\])?\s\d{1,}KB\s(yENC)?|"?\s\d{1,}\sbytes?|(\-\s)?\d{1,}(\.|,)?\d{1,}\s(g|k|m)?B\s\-?(\s?yenc)?|\s\(d{1,3},\d{1,3}\s{K,M,G}B\)\s|yEnc \d+k$|{\d+ yEnc bytes}|yEnc \d+ |\(\d+ ?(k|m|g)?b(ytes)?\) yEnc/i', ' ', $cleansubject);
			//Random stuff.
			$cleansubject = preg_replace('/AutoRarPar\d{1,5}|\(\d+\)( |  )yEnc|\d+(Amateur|Classic)| \d{4,}[a-z]{4,} |part\d+/i', ' ', $cleansubject);
			$cleansubject = utf8_encode(trim(preg_replace('/\s\s+/i', ' ', $cleansubject)));
			
			if ($type == "split")
			{
				$one = $two = "";
				if (preg_match('/"(.+?)\.[a-z0-9].+?"/i', $subject, $matches))
					$one = $matches[1];
				else if(preg_match('/s\d{1,3}[.-_ ]?(e|d)\d{1,3}|EP[\.\-_ ]?\d{1,3}[\.\-_ ]|[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!](19|20)\d\d[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!]/i', $subject, $matches2))
					$two = $matches2[0];
				return $cleansubject.$one.$two;
			}
			else if ($type !== "split" && (strlen($cleansubject) <= 7 || preg_match('/^[a-z0-9 \-\$]{1,9}$/i', $cleansubject)))
			{
				$one = $two = "";
				if (preg_match('/.+?"(.+?)".+?".+?".+/', $subject, $matches))
					$one = $matches[1];
				else if (preg_match('/(^|.+)"(.+?)(\d{2,3} ?\(\d{4}\).+?)?\.[a-z0-9].+?"/i', $subject, $matches))
					$one = $matches[2];
				if(preg_match('/s\d{1,3}[.-_ ]?(e|d)\d{1,3}|EP[\.\-_ ]?\d{1,3}[\.\-_ ]|[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!](19|20)\d\d[a-z0-9\.\-_ \(\[\)\]{}<>,"\'\$^\&\*\!]/i', $subject, $matches2))
					$two = $matches2[0];
				if ($one == "" && $two == "")
				{
					$newname = preg_replace('/[a-z0-9]/i', '', $subject);
					if (preg_match('/[\!@#\$%\^&\*\(\)\-={}\[\]\|\\:;\'<>\,\?\/_ ]{1,3}/', $newname, $matches3))
						return $cleansubject.$matches3[0];
				}
				else
					return $cleansubject.$one.$two;
			}
			else
				return $cleansubject;
		}
		
		//
		//	Cleans a usenet subject before inserting, used for searchname. Also used for imports.
		//
		public function releaseCleaner($subject, $groupID="")
		{
			if ($groupID !== "")
			{
				$groups = new Groups();
				$groupName = $groups->getByNameByID($groupID);
				
				/*if (preg_match('/alt\.binaries\.teevee/', $groupName))
				{
					//[140654]-[FULL]-[a.b.teevee]-[ Formula1.2013.Monaco.Grand.Prix.Practice.Three.720p.HDTV.x264-FAIRPLAY ]-[02/63] - "fairplay.formula1.2013.monaco.grand.prix.practice.three.720p.sample.par2" yEnc
					$cleanerName = "";
					if (preg_match('/^.+? (.+?) \]-/', $subject, $match))
						$cleanerName = $match[1];
					else
						$cleanerName = $this->releaseCleanerHelper($subject);
					
					if (empty($cleanerName)) {return $subject;}
					else {return $cleanerName;}
				}
				else*/
					return $this->releaseCleanerHelper($subject);
			}
			else
				return $this->releaseCleanerHelper($subject);
		}

		public function releaseCleanerHelper($subject)
		{
			//File and part count.
			$cleanerName = preg_replace('/(File )?(\(|\[|\s)\d{1,4}(\/|(\s|_)of(\s|_)|\-)\d{1,4}(\)|\]|\s|$|:)|\(\d{1,3}\|\d{1,3}\)|\-\d{1,3}\-\d{1,3}\.|\s\d{1,3}\sof\s\d{1,3}\.|\s\d{1,3}\/\d{1,3}|\d{1,3}of\d{1,3}\.|^\d{1,3}\/\d{1,3}\s|\d{1,3} - of \d{1,3}/i', ' ', $subject);
			//Size.
			$cleanerName = preg_replace('/\d{1,3}(\.|,)\d{1,3}\s(K|M|G)B|\d{1,}(K|M|G)B|\d{1,}\sbytes?|(\-\s)?\d{1,}(\.|,)?\d{1,}\s(g|k|m)?B\s\-(\syenc)?|\s\(d{1,3},\d{1,3}\s{K,M,G}B\)\s|\(\d+K\)\syEnc|yEnc \d+k$/i', ' ', $cleanerName);
			//Extensions.
			$cleanerName = preg_replace('/ [a-z0-9]+\.jpg|((\d{1,3})?\.part(\d{1,5})?|\d{1,5}|sample)?\.(7z|\d{3}(?=(\s|"))|avi|epub|idx|iso|jpg|m4a|mds|mkv|mobi|mp4|nfo|nzb|pdf|rar|rev|rtf|r\d\d|sfv|srs|srr|sub|txt|vol.+(par2)|par(\s?2|")|zip|z{2})"?|(\s|(\d{2,3})?\-)\d{2,3}\.mp3|\d{2,3}\.pdf|yEnc|\.part\d{1,4}\./i', ' ', $cleanerName);
			//Books + Music.
			$cleanerName = preg_replace('/((\d{1,2}-\d{1-2})?-[a-z0-9]+)?\.scr|Ebook\-[a-z0-9]+|\((\d+ )ebooks\)|\(ebooks[\.\-_ ](collection|\d+)\)|\([a-z]{3,9} \d{1,2},? 20\d\d\)|\(\d{1,2} [a-z]{3,9} 20\d\d|\[ATTN:.+?\]|ATTN: [a-z]{3,13} |ATTN:(macserv 100|Test)|ATTN: .+? - ("|:)|ATTN .+?:|\((bad conversion|Day\d{1,}\/\?|djvu|fixed|pdb|tif)\)|by [a-z0-9]{3,15}$|^Dutch(:| )|enjoy!|(\*| )enjoy(\*| )|^ePub |\(EPUB\+MOBI\)|(Flood )?Please - All I have|isbn\d+|New Ebooks \d{1,2} [a-z]{3,9} (19|20)\d\d( part \d)?|\[(MF|Ssc)\]|^New Version( - .+? - )?|^NEW( [a-z]+( Paranormal Romance|( [a-z]+)?:|,| ))?(?![\.\-_ ]York)|[\.\-_ ]NMR \d{2,3}|( |\[)NMR( |\])|\[op.+?\d\]|\[Orion_Me\]|\[ORLY\]|Please\.\.\.|R4 - Book of the Week|Re: |READNFO|Req: |Req\.|!<-- REQ:|^Request|Requesting|Should I continue posting these collections\?|\[Team [a-z0-9]+\]|[\.\-_ ](Thanks|TIA!)[\.\-_ ]|\(v\.?\d+\.\d+[a-z]?\)|par2 set|\.(j|f|m|a|s|o|n|d)[a-z]{2,8}\.20\d\d/i', ' ', $cleanerName);
			//Unwanted stuff.
			$cleanerName = preg_replace('/sample("| )?$|"sample|\(\?\?\?\?\)|\[AoU\]|AsianDVDClub\.org|AutoRarPar\d{1,5}|brothers\-of\-usenet\.(info|net)(\/\.net)?|~bY ([a-z]{3,15}|c-w)|By request|DVD-Freak|Ew-Free-Usenet-\d{1,5}|for\.usenet4ever\.info|ghost-of-usenet.org<<|GOU<<|(http:\/\/www\.)?friends-4u\.nl|\[\d+\]-\[abgxEFNET\]-|\[[a-z\d]+\]\-\[[a-z\d]+\]-\[FULL\]-|\[\d{3,}\]-\[FULL\]-\[(a\.b| abgx).+?\]|\[\d{1,}\]|\-\[FULL\].+?#a\.b[\w.#!@$%^&*\(\){}\|\\:"\';<>,?~` ]+\]|Lords-of-Usenet(\] <> presents)?|nzbcave\.co\.uk( VIP)?|(Partner (of|von) )?SSL\-News\.info>> presents|\/ post: |powere?d by (4ux(\.n\)?l)?|the usenet)|(www\.)?ssl-news(\.info)?|SSL - News\.Info|usenet-piraten\.info|\-\s\[.+?\]\s<>\spresents|<.+?https:\/\/secretusenet\.com>|SECTIONED brings you|team-hush\.org\/| TiMnZb |<TOWN>|www\.binnfo\.in|www\.dreameplace\.biz|wwwworld\.me|www\.town\.ag|(Draak48|Egbert47|jipenjans|Taima) post voor u op|Dik Trom post voor|Sponsored\.by\.SecretUsenet\.com|(::::)?UR-powered by SecretUsenet.com(::::)?|usenet4ever\.info|(www\.)?usenet-4all\.info|www\.torentz\.3xforum\.ro|usenet\-space\-cowboys\.info|> USC <|SecretUsenet\.com|Thanks to OP|\] und \[|www\.michael-kramer\.com|(http:\\\\\\\\)?www(\.| )[a-z0-9]+(\.| )(co(\.| )cc|com|info|net|org)|zoekt nog posters\/spotters|>> presents|Z\[?NZB\]?(\.|_)wz(\.|_)cz|partner[\.\-_ ]of([\.\-_ ]www)?/i', ' ', $cleanerName);
			//Change [pw] to passworded.
			$cleanerName = str_replace(array('[pw]', '[PW]', ' PW ', '(Password)'), ' PASSWORDED ', $cleanerName);
			//Replaces some characters with 1 space.
			$cleanerName = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "'", "~", "/", "&", "+"), " ", $cleanerName);
			//Replace multiple spaces with 1 space
			$cleanerName = trim(preg_replace('/\s\s+/i', ' ', $cleanerName));
			//Remove the double name.
			$cleanerName = implode(' ', array_intersect_key(explode(' ', $cleanerName), array_unique(array_map('strtolower', explode(' ', $cleanerName)))));
			
			if (empty($cleanerName)) {return $subject;}
			else {return $cleanerName;}
		}

		//
		//	Cleans release name for the namefixer class.
		//
		public function fixerCleaner($name)
		{
			//Extensions.
			$cleanerName = preg_replace('/ [a-z0-9]+\.jpg|((\d{1,3})?\.part(\d{1,5})?|\d{1,5}|sample)?\.(7z|\d{3}(?=(\s|"))|avi|epub|idx|iso|jpg|m4a|mds|mkv|mobi|mp4|nfo|nzb|pdf|rar|rev|rtf|r\d\d|sfv|srs|srr|sub|txt|vol.+(par2)|par(\s?2|")|zip|z{2})"?|(\s|(\d{2,3})?\-)\d{2,3}\.mp3|\d{2,3}\.pdf|yEnc|\.part\d{1,4}\./i', ' ', $name);
			//Replaces some characters with 1 space.
			$cleanerName = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "'", "~", "/", "&", "+"), " ", $cleanerName);
			//Replace multiple spaces with 1 space
			$cleanerName = preg_replace('/\s\s+/i', ' ', $cleanerName);
			//Remove invalid characters.
			$cleanerName = trim(utf8_encode(preg_replace('/[^(\x20-\x7F)]*/','', $cleanerName)));
			
			return $cleanerName;
		}
	}
?>
