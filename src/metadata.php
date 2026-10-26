<?php

/**
 * Plugin Information File
 *
 * Provides metadata for the JSON Web Tokens plugin, allowing plugin
 * manager and marketplace to identify, display, and manage it.
 *
 * @package     CiSkeleton\Plugins
 * @subpackage  JWT
 * @category    Metadata
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     Apache-2.0 — Apache License 2.0
 * @link        https://www.apache.org/licenses/LICENSE-2.0
 *
 * SPDX-License-Identifier: Apache-2.0
 */

return [
	// Info
	'name' => 'JSON Web Tokens (JWT)',
	'description' => 'Provides secure token-based authentication for APIs and user sessions using the JWT standard.',
	'version' => '0.0.1',
	'version_id' => 1,
	'repository' => 'https://github.com/ciskeleton/plugin-jwt',

	// Presentation.
	'icon' => 'key',
	'icon_fg' => '#ffffff',
	'icon_bg' => '#6435c9',

	// Author.
	'author' => [
		'name' => Platform::AUTHOR,
		'url' => Platform::AUTHOR_URL,
	],

	// License.
	'license' => [
		'name' => 'Apache 2.0',
		'url' => 'https://www.apache.org/licenses/LICENSE-2.0',
	],

	// Translations.
	'translations' => [
		// Arabic
		'arabic' => [
			'name' => 'رموز JSON المميزة (JWT)',
			'description' => 'يوفّر نظام مصادقة آمن يعتمد على الرموز (Token) وفق معيار JWT للمستخدمين وواجهات البرمجة.',
		],

		// Armenian
		'armenian' => [
			'name' => 'JSON վեբ թոքեններ (JWT)',
			'description' => 'Տրամադրում է անվտանգ թոքենային նույնականացում API-ների և օգտատերերի սեսիաների համար՝ օգտագործելով JWT ստանդարտը։',
		],

		// Azerbaijani
		'azerbaijani' => [
			'name' => 'JSON Web Tokens (JWT)',
			'description' => 'JWT standartından istifadə etməklə API-lər və istifadəçi sessiyaları üçün təhlükəsiz, token əsaslı autentifikasiyanı təmin edir.',
		],

		// Bengali
		'bengali' => [
			'name' => 'JSON ওয়েব টোকেন (JWT)',
			'description' => 'JWT মান ব্যবহার করে API ও ব্যবহারকারী সেশনের জন্য নিরাপদ টোকেন-ভিত্তিক প্রমাণীকরণ প্রদান করে।',
		],

		// Brazilian Portuguese
		'brazilian' => [
			'name' => 'Tokens JWT (JSON Web Tokens)',
			'description' => 'Fornece autenticação segura baseada em tokens para APIs e sessões de usuário usando o padrão JWT.',
		],

		// Bulgarian
		'bulgarian' => [
			'name' => 'JSON уеб токени (JWT)',
			'description' => 'Осигурява сигурна автентикация с токени за API и потребителски сесии чрез стандарта JWT.',
		],

		// Catalan
		'catalan' => [
			'name' => 'Tokens web JSON (JWT)',
			'description' => 'Proporciona autenticació segura basada en tokens per a APIs i sessions d’usuari mitjançant l’estàndard JWT.',
		],

		// Simplified Chinese
		'chinese' => [
			'name' => 'JSON 网页令牌 (JWT)',
			'description' => '使用 JWT 标准为 API 和用户会话提供安全的基于令牌的身份验证。',
		],

		// Croatian
		'croatian' => [
			'name' => 'JSON web tokeni (JWT)',
			'description' => 'Pruža sigurnu autentifikaciju temeljenu na tokenima za API-je i korisničke sesije koristeći JWT standard.',
		],

		// Czech
		'czech' => [
			'name' => 'JSON webové tokeny (JWT)',
			'description' => 'Poskytuje zabezpečené ověřování založené na tokenech pro API a uživatelské relace pomocí standardu JWT.',
		],

		// Danish
		'danish' => [
			'name' => 'JSON-webtokens (JWT)',
			'description' => 'Leverer sikker tokenbaseret godkendelse til APIs og brugersessioner ved hjælp af JWT-standarden.',
		],

		// Dutch
		'dutch' => [
			'name' => 'JSON-webtokens (JWT)',
			'description' => 'Biedt veilige tokengebaseerde authenticatie voor API’s en gebruikerssessies met behulp van de JWT-standaard.',
		],

		// Estonian
		'estonian' => [
			'name' => 'JSON veebitokenid (JWT)',
			'description' => 'Pakub turvalist tokenipõhist autentimist API-dele ja kasutajaseanssidele JWT standardi abil.',
		],

		// Filipino
		'filipino' => [
			'name' => 'JSON Web Token (JWT)',
			'description' => 'Nagbibigay ng ligtas na token-based na pagpapatotoo para sa APIs at mga session ng user gamit ang pamantayang JWT.',
		],

		// Finnish
		'finnish' => [
			'name' => 'JSON-verkkotunnisteet (JWT)',
			'description' => 'Tarjoaa turvallisen tokenpohjaisen todennuksen API-rajapinnoille ja käyttäjäistunnoille JWT-standardin avulla.',
		],

		// French
		'french' => [
			'name' => 'Jetons web JSON (JWT)',
			'description' => 'Fournit une authentification sécurisée par jeton pour les API et les sessions utilisateur selon la norme JWT.',
		],

		// Georgian
		'georgian' => [
			'name' => 'JSON ვებ ტოკენები (JWT)',
			'description' => 'უზრუნველყოფს უსაფრთხო ტოკენზე დაფუძნებულ ავთენტიფიკაციას APIs-ისა და მომხმარებლის სესიებისთვის JWT სტანდარტის გამოყენებით.',
		],

		// German
		'german' => [
			'name' => 'JSON-Webtokens (JWT)',
			'description' => 'Bietet sichere tokenbasierte Authentifizierung für APIs und Benutzersitzungen nach dem JWT-Standard.',
		],

		// Greek
		'greek' => [
			'name' => 'Διακριτικά ιστού JSON (JWT)',
			'description' => 'Παρέχει ασφαλή αυθεντικοποίηση βάσει token για APIs και συνεδρίες χρηστών χρησιμοποιώντας το πρότυπο JWT.',
		],

		// Hindi
		'hindi' => [
			'name' => 'JSON वेब टोकन (JWT)',
			'description' => 'JWT मानक का उपयोग करते हुए API और उपयोगकर्ता सत्रों के लिए सुरक्षित टोकन-आधारित प्रमाणीकरण प्रदान करता है।',
		],

		// Hungarian
		'hungarian' => [
			'name' => 'JSON webes tokenek (JWT)',
			'description' => 'Biztonságos tokenalapú hitelesítést biztosít APIs és felhasználói munkamenetek számára a JWT szabvány használatával.',
		],

		// Indonesian
		'indonesian' => [
			'name' => 'Token Web JSON (JWT)',
			'description' => 'Menyediakan autentikasi berbasis token yang aman untuk API dan sesi pengguna menggunakan standar JWT.',
		],

		// Italian
		'italian' => [
			'name' => 'Token web JSON (JWT)',
			'description' => 'Fornisce un’autenticazione sicura basata su token per API e sessioni utente utilizzando lo standard JWT.',
		],

		// Japanese
		'japanese' => [
			'name' => 'JSON Web トークン (JWT)',
			'description' => 'JWT 標準を使用して、API とユーザーセッションに安全なトークンベースの認証を提供します。',
		],

		// Khmer
		'khmer' => [
			'name' => 'JSON Web Tokens (JWT)',
			'description' => 'ផ្តល់នូវការផ្ទៀងផ្ទាត់ភាពត្រឹមត្រូវប្រកបដោយសុវត្ថិភាពដោយផ្អែកលើ Token សម្រាប់ API និងសម័យប្រើប្រាស់របស់អ្នកប្រើប្រាស់ (user sessions) ដោយប្រើស្តង់ដារ JWT។',
		],

		// Korean
		'korean' => [
			'name' => 'JSON 웹 토큰 (JWT)',
			'description' => 'JWT 표준을 사용하여 API 및 사용자 세션에 대한 안전한 토큰 기반 인증을 제공합니다.',
		],

		// Latvian
		'latvian' => [
			'name' => 'JSON tīmekļa tokeni (JWT)',
			'description' => 'Nodrošina drošu tokenos balstītu autentifikāciju APIs un lietotāju sesijām, izmantojot JWT standartu.',
		],

		// Lithuanian
		'lithuanian' => [
			'name' => 'JSON žiniatinklio tokenai (JWT)',
			'description' => 'Teikia saugų tokenais pagrįstą autentifikavimą APIs ir naudotojų sesijoms naudojant JWT standartą.',
		],

		// Malay
		'malay' => [
			'name' => 'Token Web JSON (JWT)',
			'description' => 'Menyediakan pengesahan berasaskan token yang selamat untuk APIs dan sesi pengguna menggunakan standard JWT.',
		],

		// Marathi
		'marathi' => [
			'name' => 'JSON Web Tokens (JWT)',
			'description' => 'JWT मानकाचा वापर करून API आणि वापरकर्ता सत्रांसाठी (user sessions) सुरक्षित टोकन-आधारित प्रमाणीकरण (authentication) प्रदान करते.',
		],

		// Norwegian
		'norwegian' => [
			'name' => 'JSON-webtoken (JWT)',
			'description' => 'Gir sikker tokenbasert autentisering for APIs og brukerøkter ved hjelp av JWT-standarden.',
		],

		// Persian
		'persian' => [
			'name' => 'توکن‌های وب JSON (JWT)',
			'description' => 'احراز هویت امن مبتنی بر توکن را برای APIها و نشست‌های کاربری با استفاده از استاندارد JWT فراهم می‌کند.',
		],

		// Polish
		'polish' => [
			'name' => 'JSON Web Tokeny (JWT)',
			'description' => 'Zapewnia bezpieczne uwierzytelnianie oparte na tokenach dla API i sesji użytkowników przy użyciu standardu JWT.',
		],

		// European Portuguese
		'portuguese' => [
			'name' => 'Tokens JWT (JSON Web Tokens)',
			'description' => 'Fornece autenticação segura baseada em tokens para APIs e sessões de utilizador usando o padrão JWT.',
		],

		// Romanian
		'romanian' => [
			'name' => 'Tokenuri web JSON (JWT)',
			'description' => 'Oferă autentificare securizată bazată pe tokenuri pentru APIs și sesiuni de utilizator folosind standardul JWT.',
		],

		// Russian
		'russian' => [
			'name' => 'Веб-токены JSON (JWT)',
			'description' => 'Обеспечивает безопасную токен-аутентификацию для API и пользовательских сессий с использованием стандарта JWT.',
		],

		// Serbian (Latin)
		'serbian' => [
			'name' => 'JSON veb tokeni (JWT)',
			'description' => 'Obezbeđuje sigurnu autentifikaciju zasnovanu na tokenima za APIs i korisničke sesije koristeći JWT standard.',
		],

		// Slovak
		'slovak' => [
			'name' => 'JSON webové tokeny (JWT)',
			'description' => 'Poskytuje bezpečné overovanie založené na tokenoch pre APIs a používateľské relácie pomocou štandardu JWT.',
		],

		// Slovenian
		'slovenian' => [
			'name' => 'JSON spletni tokeni (JWT)',
			'description' => 'Zagotavlja varno avtentikacijo na osnovi tokenov za APIs in uporabniške seje z uporabo standarda JWT.',
		],

		// Spanish
		'spanish' => [
			'name' => 'Tokens Web JSON (JWT)',
			'description' => 'Proporciona autenticación segura basada en tokens para APIs y sesiones de usuario mediante el estándar JWT.',
		],

		// Swahili
		'swahili' => [
			'name' => 'Tokeni za Wavuti za JSON (JWT)',
			'description' => 'Hutoa uthibitishaji salama unaotegemea tokeni kwa APIs na vipindi vya watumiaji kwa kutumia kiwango cha JWT.',
		],

		// Swedish
		'swedish' => [
			'name' => 'JSON-webbtokens (JWT)',
			'description' => 'Ger säker tokenbaserad autentisering för APIs och användarsessioner med JWT-standarden.',
		],

		// Traditional Chinese
		'taiwanese' => [
			'name' => 'JSON 網頁權杖 (JWT)',
			'description' => '使用 JWT 標準為 API 和使用者工作階段提供安全的權杖式驗證。',
		],

		// Tamil
		'tamil' => [
			'name' => 'JSON வலை டோக்கன்கள் (JWT)',
			'description' => 'JWT தரநிலையைப் பயன்படுத்தி APIs மற்றும் பயனர் அமர்வுகளுக்கு பாதுகாப்பான டோக்கன் அடிப்படையிலான அங்கீகாரத்தை வழங்குகிறது.',
		],

		// Thai
		'thai' => [
			'name' => 'โทเค็นเว็บ JSON (JWT)',
			'description' => 'ให้การตรวจสอบสิทธิ์แบบโทเค็นที่ปลอดภัยสำหรับ API และเซสชันของผู้ใช้โดยใช้มาตรฐาน JWT.',
		],

		// Turkish
		'turkish' => [
			'name' => 'JSON Web Jetonları (JWT)',
			'description' => 'JWT standardını kullanarak API’ler ve kullanıcı oturumları için güvenli jeton tabanlı kimlik doğrulama sağlar.',
		],

		// Ukrainian
		'ukrainian' => [
			'name' => 'Веб-токени JSON (JWT)',
			'description' => 'Надає безпечну автентифікацію на основі токенів для APIs і користувацьких сеансів за стандартом JWT.',
		],

		// Urdu
		'urdu' => [
			'name' => 'JSON ویب ٹوکنز (JWT)',
			'description' => 'JWT معیار استعمال کرتے ہوئے APIs اور صارف سیشنز کے لیے محفوظ ٹوکن پر مبنی تصدیق فراہم کرتا ہے۔',
		],

		// Vietnamese
		'vietnamese' => [
			'name' => 'JSON Web Token (JWT)',
			'description' => 'Cung cấp xác thực dựa trên mã thông báo an toàn cho API và phiên người dùng bằng tiêu chuẩn JWT.',
		],
	],
];
