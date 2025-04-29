<?php

declare(strict_types=1);

namespace Tempest\Support\Language;

use Locale as NativeLocale;

use function Tempest\Support\Str\to_lower_case;
use function Tempest\Support\Str\to_upper_case;
use function Tempest\Support\Str\upper_first;

/**
 * Represents a locale identifier.
 */
enum Locale: string
{
    case AFRIKAANS = 'af';
    case AFRIKAANS_NAMIBIA = 'af_NA';
    case AFRIKAANS_SOUTH_AFRICA = 'af_ZA';
    case AGHEM = 'agq';
    case AGHEM_CAMEROON = 'agq_CM';
    case AKAN = 'ak';
    case AKAN_GHANA = 'ak_GH';
    case AMHARIC = 'am';
    case AMHARIC_ETHIOPIA = 'am_ET';
    case ARABIC = 'ar';
    case ARABIC_UNITED_ARAB_EMIRATES = 'ar_AE';
    case ARABIC_BAHRAIN = 'ar_BH';
    case ARABIC_DJIBOUTI = 'ar_DJ';
    case ARABIC_ALGERIA = 'ar_DZ';
    case ARABIC_EGYPT = 'ar_EG';
    case ARABIC_WESTERN_SAHARA = 'ar_EH';
    case ARABIC_ERITREA = 'ar_ER';
    case ARABIC_ISRAEL = 'ar_IL';
    case ARABIC_IRAQ = 'ar_IQ';
    case ARABIC_JORDAN = 'ar_JO';
    case ARABIC_COMOROS = 'ar_KM';
    case ARABIC_KUWAIT = 'ar_KW';
    case ARABIC_LEBANON = 'ar_LB';
    case ARABIC_LIBYA = 'ar_LY';
    case ARABIC_MOROCCO = 'ar_MA';
    case ARABIC_MAURITANIA = 'ar_MR';
    case ARABIC_OMAN = 'ar_OM';
    case ARABIC_PALESTINE = 'ar_PS';
    case ARABIC_QATAR = 'ar_QA';
    case ARABIC_SAUDI_ARABIA = 'ar_SA';
    case ARABIC_SUDAN = 'ar_SD';
    case ARABIC_SOMALIA = 'ar_SO';
    case ARABIC_SOUTH_SUDAN = 'ar_SS';
    case ARABIC_SYRIA = 'ar_SY';
    case ARABIC_CHAD = 'ar_TD';
    case ARABIC_TUNISIA = 'ar_TN';
    case ARABIC_YEMEN = 'ar_YE';
    case ASSAMESE = 'as';
    case ASSAMESE_INDIA = 'as_IN';
    case ASU = 'asa';
    case ASU_TANZANIA = 'asa_TZ';
    case ASTURIAN = 'ast';
    case ASTURIAN_SPAIN = 'ast_ES';
    case AZERBAIJANI = 'az';
    case AZERBAIJANI_CYRILLIC = 'az_Cyrl';
    case AZERBAIJANI_LATIN = 'az_Latn';
    case BASAA = 'bas';
    case BASAA_CAMEROON = 'bas_CM';
    case BELARUSIAN = 'be';
    case BELARUSIAN_BELARUS = 'be_BY';
    case BEMBA = 'bem';
    case BEMBA_ZAMBIA = 'bem_ZM';
    case BENA = 'bez';
    case BENA_TANZANIA = 'bez_TZ';
    case BULGARIAN = 'bg';
    case BULGARIAN_BULGARIA = 'bg_BG';
    case HARYANVI = 'bgc';
    case HARYANVI_INDIA = 'bgc_IN';
    case BHOJPURI = 'bho';
    case BHOJPURI_INDIA = 'bho_IN';
    case ANII = 'blo';
    case ANII_BENIN = 'blo_BJ';
    case BAMBARA = 'bm';
    case BAMBARA_MALI = 'bm_ML';
    case BANGLA = 'bn';
    case BANGLA_BANGLADESH = 'bn_BD';
    case BANGLA_INDIA = 'bn_IN';
    case TIBETAN = 'bo';
    case TIBETAN_CHINA = 'bo_CN';
    case TIBETAN_INDIA = 'bo_IN';
    case BRETON = 'br';
    case BRETON_FRANCE = 'br_FR';
    case BODO = 'brx';
    case BODO_INDIA = 'brx_IN';
    case BOSNIAN = 'bs';
    case BOSNIAN_CYRILLIC = 'bs_Cyrl';
    case BOSNIAN_LATIN = 'bs_Latn';
    case CATALAN = 'ca';
    case CATALAN_ANDORRA = 'ca_AD';
    case CATALAN_SPAIN = 'ca_ES';
    case CATALAN_FRANCE = 'ca_FR';
    case CATALAN_ITALY = 'ca_IT';
    case CHAKMA = 'ccp';
    case CHAKMA_BANGLADESH = 'ccp_BD';
    case CHAKMA_INDIA = 'ccp_IN';
    case CHECHEN = 'ce';
    case CHECHEN_RUSSIA = 'ce_RU';
    case CEBUANO = 'ceb';
    case CEBUANO_PHILIPPINES = 'ceb_PH';
    case CHIGA = 'cgg';
    case CHIGA_UGANDA = 'cgg_UG';
    case CHEROKEE = 'chr';
    case CHEROKEE_UNITED_STATES = 'chr_US';
    case CENTRAL_KURDISH = 'ckb';
    case CENTRAL_KURDISH_IRAQ = 'ckb_IQ';
    case CENTRAL_KURDISH_IRAN = 'ckb_IR';
    case CZECH = 'cs';
    case CZECH_CZECHIA = 'cs_CZ';
    case SWAMPY_CREE = 'csw';
    case SWAMPY_CREE_CANADA = 'csw_CA';
    case CHUVASH = 'cv';
    case CHUVASH_RUSSIA = 'cv_RU';
    case WELSH = 'cy';
    case WELSH_UNITED_KINGDOM = 'cy_GB';
    case DANISH = 'da';
    case DANISH_DENMARK = 'da_DK';
    case DANISH_GREENLAND = 'da_GL';
    case TAITA = 'dav';
    case TAITA_KENYA = 'dav_KE';
    case GERMAN = 'de';
    case GERMAN_AUSTRIA = 'de_AT';
    case GERMAN_BELGIUM = 'de_BE';
    case GERMAN_SWITZERLAND = 'de_CH';
    case GERMAN_GERMANY = 'de_DE';
    case GERMAN_ITALY = 'de_IT';
    case GERMAN_LIECHTENSTEIN = 'de_LI';
    case GERMAN_LUXEMBOURG = 'de_LU';
    case ZARMA = 'dje';
    case ZARMA_NIGER = 'dje_NE';
    case DOGRI = 'doi';
    case DOGRI_INDIA = 'doi_IN';
    case LOWER_SORBIAN = 'dsb';
    case LOWER_SORBIAN_GERMANY = 'dsb_DE';
    case DUALA = 'dua';
    case DUALA_CAMEROON = 'dua_CM';
    case JOLA_FONYI = 'dyo';
    case JOLA_FONYI_SENEGAL = 'dyo_SN';
    case DZONGKHA = 'dz';
    case DZONGKHA_BHUTAN = 'dz_BT';
    case EMBU = 'ebu';
    case EMBU_KENYA = 'ebu_KE';
    case EWE = 'ee';
    case EWE_GHANA = 'ee_GH';
    case EWE_TOGO = 'ee_TG';
    case GREEK = 'el';
    case GREEK_CYPRUS = 'el_CY';
    case GREEK_GREECE = 'el_GR';
    case ENGLISH = 'en';
    case ENGLISH_UNITED_ARAB_EMIRATES = 'en_AE';
    case ENGLISH_ANTIGUA_BARBUDA = 'en_AG';
    case ENGLISH_ANGUILLA = 'en_AI';
    case ENGLISH_AMERICAN_SAMOA = 'en_AS';
    case ENGLISH_AUSTRIA = 'en_AT';
    case ENGLISH_AUSTRALIA = 'en_AU';
    case ENGLISH_BARBADOS = 'en_BB';
    case ENGLISH_BELGIUM = 'en_BE';
    case ENGLISH_BURUNDI = 'en_BI';
    case ENGLISH_BERMUDA = 'en_BM';
    case ENGLISH_BAHAMAS = 'en_BS';
    case ENGLISH_BOTSWANA = 'en_BW';
    case ENGLISH_BELIZE = 'en_BZ';
    case ENGLISH_CANADA = 'en_CA';
    case ENGLISH_COCOS_KEELING_ISLANDS = 'en_CC';
    case ENGLISH_SWITZERLAND = 'en_CH';
    case ENGLISH_COOK_ISLANDS = 'en_CK';
    case ENGLISH_CAMEROON = 'en_CM';
    case ENGLISH_CHRISTMAS_ISLAND = 'en_CX';
    case ENGLISH_CYPRUS = 'en_CY';
    case ENGLISH_GERMANY = 'en_DE';
    case ENGLISH_DIEGO_GARCIA = 'en_DG';
    case ENGLISH_DENMARK = 'en_DK';
    case ENGLISH_DOMINICA = 'en_DM';
    case ENGLISH_ERITREA = 'en_ER';
    case ENGLISH_FINLAND = 'en_FI';
    case ENGLISH_FIJI = 'en_FJ';
    case ENGLISH_FALKLAND_ISLANDS = 'en_FK';
    case ENGLISH_MICRONESIA = 'en_FM';
    case ENGLISH_UNITED_KINGDOM = 'en_GB';
    case ENGLISH_GRENADA = 'en_GD';
    case ENGLISH_GUERNSEY = 'en_GG';
    case ENGLISH_GHANA = 'en_GH';
    case ENGLISH_GIBRALTAR = 'en_GI';
    case ENGLISH_GAMBIA = 'en_GM';
    case ENGLISH_GUAM = 'en_GU';
    case ENGLISH_GUYANA = 'en_GY';
    case ENGLISH_HONG_KONG_SAR_CHINA = 'en_HK';
    case ENGLISH_INDONESIA = 'en_ID';
    case ENGLISH_IRELAND = 'en_IE';
    case ENGLISH_ISRAEL = 'en_IL';
    case ENGLISH_ISLEOF_MAN = 'en_IM';
    case ENGLISH_INDIA = 'en_IN';
    case ENGLISH_BRITISH_INDIAN_OCEAN_TERRITORY = 'en_IO';
    case ENGLISH_JERSEY = 'en_JE';
    case ENGLISH_JAMAICA = 'en_JM';
    case ENGLISH_KENYA = 'en_KE';
    case ENGLISH_KIRIBATI = 'en_KI';
    case ENGLISH_ST_KITTS_NEVIS = 'en_KN';
    case ENGLISH_CAYMAN_ISLANDS = 'en_KY';
    case ENGLISH_ST_LUCIA = 'en_LC';
    case ENGLISH_LIBERIA = 'en_LR';
    case ENGLISH_LESOTHO = 'en_LS';
    case ENGLISH_MADAGASCAR = 'en_MG';
    case ENGLISH_MARSHALL_ISLANDS = 'en_MH';
    case ENGLISH_MACAO_SAR_CHINA = 'en_MO';
    case ENGLISH_NORTHERN_MARIANA_ISLANDS = 'en_MP';
    case ENGLISH_MONTSERRAT = 'en_MS';
    case ENGLISH_MALTA = 'en_MT';
    case ENGLISH_MAURITIUS = 'en_MU';
    case ENGLISH_MALDIVES = 'en_MV';
    case ENGLISH_MALAWI = 'en_MW';
    case ENGLISH_MALAYSIA = 'en_MY';
    case ENGLISH_NAMIBIA = 'en_NA';
    case ENGLISH_NORFOLK_ISLAND = 'en_NF';
    case ENGLISH_NIGERIA = 'en_NG';
    case ENGLISH_NETHERLANDS = 'en_NL';
    case ENGLISH_NAURU = 'en_NR';
    case ENGLISH_NIUE = 'en_NU';
    case ENGLISH_NEW_ZEALAND = 'en_NZ';
    case ENGLISH_PAPUA_NEW_GUINEA = 'en_PG';
    case ENGLISH_PHILIPPINES = 'en_PH';
    case ENGLISH_PAKISTAN = 'en_PK';
    case ENGLISH_PITCAIRN_ISLANDS = 'en_PN';
    case ENGLISH_PUERTO_RICO = 'en_PR';
    case ENGLISH_PALAU = 'en_PW';
    case ENGLISH_RWANDA = 'en_RW';
    case ENGLISH_SOLOMON_ISLANDS = 'en_SB';
    case ENGLISH_SEYCHELLES = 'en_SC';
    case ENGLISH_SUDAN = 'en_SD';
    case ENGLISH_SWEDEN = 'en_SE';
    case ENGLISH_SINGAPORE = 'en_SG';
    case ENGLISH_ST_HELENA = 'en_SH';
    case ENGLISH_SLOVENIA = 'en_SI';
    case ENGLISH_SIERRA_LEONE = 'en_SL';
    case ENGLISH_SOUTH_SUDAN = 'en_SS';
    case ENGLISH_SINT_MAARTEN = 'en_SX';
    case ENGLISH_ESWATINI = 'en_SZ';
    case ENGLISH_TURKS_CAICOS_ISLANDS = 'en_TC';
    case ENGLISH_TOKELAU = 'en_TK';
    case ENGLISH_TONGA = 'en_TO';
    case ENGLISH_TRINIDAD_TOBAGO = 'en_TT';
    case ENGLISH_TUVALU = 'en_TV';
    case ENGLISH_TANZANIA = 'en_TZ';
    case ENGLISH_UGANDA = 'en_UG';
    case ENGLISH_US_OUTLYING_ISLANDS = 'en_UM';
    case ENGLISH_UNITED_STATES = 'en_US';
    case ENGLISH_ST_VINCENT_GRENADINES = 'en_VC';
    case ENGLISH_BRITISH_VIRGIN_ISLANDS = 'en_VG';
    case ENGLISH_US_VIRGIN_ISLANDS = 'en_VI';
    case ENGLISH_VANUATU = 'en_VU';
    case ENGLISH_SAMOA = 'en_WS';
    case ENGLISH_SOUTH_AFRICA = 'en_ZA';
    case ENGLISH_ZAMBIA = 'en_ZM';
    case ENGLISH_ZIMBABWE = 'en_ZW';
    case ESPERANTO = 'eo';
    case SPANISH = 'es';
    case SPANISH_ARGENTINA = 'es_AR';
    case SPANISH_BOLIVIA = 'es_BO';
    case SPANISH_BRAZIL = 'es_BR';
    case SPANISH_BELIZE = 'es_BZ';
    case SPANISH_CHILE = 'es_CL';
    case SPANISH_COLOMBIA = 'es_CO';
    case SPANISH_COSTA_RICA = 'es_CR';
    case SPANISH_CUBA = 'es_CU';
    case SPANISH_DOMINICAN_REPUBLIC = 'es_DO';
    case SPANISH_CEUTA_MELILLA = 'es_EA';
    case SPANISH_ECUADOR = 'es_EC';
    case SPANISH_SPAIN = 'es_ES';
    case SPANISH_EQUATORIAL_GUINEA = 'es_GQ';
    case SPANISH_GUATEMALA = 'es_GT';
    case SPANISH_HONDURAS = 'es_HN';
    case SPANISH_CANARY_ISLANDS = 'es_IC';
    case SPANISH_MEXICO = 'es_MX';
    case SPANISH_NICARAGUA = 'es_NI';
    case SPANISH_PANAMA = 'es_PA';
    case SPANISH_PERU = 'es_PE';
    case SPANISH_PHILIPPINES = 'es_PH';
    case SPANISH_PUERTO_RICO = 'es_PR';
    case SPANISH_PARAGUAY = 'es_PY';
    case SPANISH_EL_SALVADOR = 'es_SV';
    case SPANISH_UNITED_STATES = 'es_US';
    case SPANISH_URUGUAY = 'es_UY';
    case SPANISH_VENEZUELA = 'es_VE';
    case ESTONIAN = 'et';
    case ESTONIAN_ESTONIA = 'et_EE';
    case BASQUE = 'eu';
    case BASQUE_SPAIN = 'eu_ES';
    case EWONDO = 'ewo';
    case EWONDO_CAMEROON = 'ewo_CM';
    case PERSIAN = 'fa';
    case PERSIAN_AFGHANISTAN = 'fa_AF';
    case PERSIAN_IRAN = 'fa_IR';
    case FULA = 'ff';
    case FULA_ADLAM = 'ff_Adlm';
    case FULA_LATIN = 'ff_Latn';
    case FULA_LATIN_NIGERIA = 'ff_Latn_NG';
    case FULA_LATIN_SENEGAL = 'ff_Latn_SG';
    case FINNISH = 'fi';
    case FINNISH_FINLAND = 'fi_FI';
    case FILIPINO = 'fil';
    case FILIPINO_PHILIPPINES = 'fil_PH';
    case FAROESE = 'fo';
    case FAROESE_DENMARK = 'fo_DK';
    case FAROESE_FAROE_ISLANDS = 'fo_FO';
    case FRENCH = 'fr';
    case FRENCH_BELGIUM = 'fr_BE';
    case FRENCH_BURKINA_FASO = 'fr_BF';
    case FRENCH_BURUNDI = 'fr_BI';
    case FRENCH_BENIN = 'fr_BJ';
    case FRENCH_ST_BARTHELEMY = 'fr_BL';
    case FRENCH_CANADA = 'fr_CA';
    case FRENCH_CONGO_KINSHASA = 'fr_CD';
    case FRENCH_CENTRAL_AFRICAN_REPUBLIC = 'fr_CF';
    case FRENCH_CONGO_BRAZZAVILLE = 'fr_CG';
    case FRENCH_SWITZERLAND = 'fr_CH';
    case FRENCH_COTED_IVOIRE = 'fr_CI';
    case FRENCH_CAMEROON = 'fr_CM';
    case FRENCH_DJIBOUTI = 'fr_DJ';
    case FRENCH_ALGERIA = 'fr_DZ';
    case FRENCH_FRANCE = 'fr_FR';
    case FRENCH_GABON = 'fr_GA';
    case FRENCH_FRENCH_GUIANA = 'fr_GF';
    case FRENCH_GUINEA = 'fr_GN';
    case FRENCH_GUADELOUPE = 'fr_GP';
    case FRENCH_EQUATORIAL_GUINEA = 'fr_GQ';
    case FRENCH_HAITI = 'fr_HT';
    case FRENCH_COMOROS = 'fr_KM';
    case FRENCH_LUXEMBOURG = 'fr_LU';
    case FRENCH_MOROCCO = 'fr_MA';
    case FRENCH_MONACO = 'fr_MC';
    case FRENCH_ST_MARTIN = 'fr_MF';
    case FRENCH_MADAGASCAR = 'fr_MG';
    case FRENCH_MALI = 'fr_ML';
    case FRENCH_MARTINIQUE = 'fr_MQ';
    case FRENCH_MAURITANIA = 'fr_MR';
    case FRENCH_MAURITIUS = 'fr_MU';
    case FRENCH_NEW_CALEDONIA = 'fr_NC';
    case FRENCH_NIGER = 'fr_NE';
    case FRENCH_FRENCH_POLYNESIA = 'fr_PF';
    case FRENCH_ST_PIERRE_MIQUELON = 'fr_PM';
    case FRENCH_REUNION = 'fr_RE';
    case FRENCH_RWANDA = 'fr_RW';
    case FRENCH_SEYCHELLES = 'fr_SC';
    case FRENCH_SENEGAL = 'fr_SN';
    case FRENCH_SYRIA = 'fr_SY';
    case FRENCH_CHAD = 'fr_TD';
    case FRENCH_TOGO = 'fr_TG';
    case FRENCH_TUNISIA = 'fr_TN';
    case FRENCH_VANUATU = 'fr_VU';
    case FRENCH_WALLIS_FUTUNA = 'fr_WF';
    case FRENCH_MAYOTTE = 'fr_YT';
    case FRIULIAN = 'fur';
    case FRIULIAN_ITALY = 'fur_IT';
    case WESTERN_FRISIAN = 'fy';
    case WESTERN_FRISIAN_NETHERLANDS = 'fy_NL';
    case IRISH = 'ga';
    case IRISH_UNITED_KINGDOM = 'ga_GB';
    case IRISH_IRELAND = 'ga_IE';
    case SCOTTISH_GAELIC = 'gd';
    case SCOTTISH_GAELIC_UNITED_KINGDOM = 'gd_GB';
    case GALICIAN = 'gl';
    case GALICIAN_SPAIN = 'gl_ES';
    case SWISS_GERMAN = 'gsw';
    case SWISS_GERMAN_SWITZERLAND = 'gsw_CH';
    case SWISS_GERMAN_FRANCE = 'gsw_FR';
    case SWISS_GERMAN_LIECHTENSTEIN = 'gsw_LI';
    case GUJARATI = 'gu';
    case GUJARATI_INDIA = 'gu_IN';
    case GUSII = 'guz';
    case GUSII_KENYA = 'guz_KE';
    case MANX = 'gv';
    case MANX_ISLEOF_MAN = 'gv_IM';
    case HAUSA = 'ha';
    case HAUSA_GHANA = 'ha_GH';
    case HAUSA_NIGER = 'ha_NE';
    case HAUSA_NIGERIA = 'ha_NG';
    case HAWAIIAN = 'haw';
    case HAWAIIAN_UNITED_STATES = 'haw_US';
    case HEBREW = 'he';
    case HEBREW_ISRAEL = 'he_IL';
    case HINDI = 'hi';
    case HINDI_INDIA = 'hi_IN';
    case HINDI_LATIN = 'hi_Latn';
    case CROATIAN = 'hr';
    case CROATIAN_BOSNIA_HERZEGOVINA = 'hr_BA';
    case CROATIAN_CROATIA = 'hr_HR';
    case UPPER_SORBIAN = 'hsb';
    case UPPER_SORBIAN_GERMANY = 'hsb_DE';
    case HUNGARIAN = 'hu';
    case HUNGARIAN_HUNGARY = 'hu_HU';
    case ARMENIAN = 'hy';
    case ARMENIAN_ARMENIA = 'hy_AM';
    case INTERLINGUA = 'ia';
    case INDONESIAN = 'id';
    case INDONESIAN_INDONESIA = 'id_ID';
    case INTERLINGUE = 'ie';
    case INTERLINGUE_ESTONIA = 'ie_EE';
    case IGBO = 'ig';
    case IGBO_NIGERIA = 'ig_NG';
    case SICHUAN_YI = 'ii';
    case SICHUAN_YI_CHINA = 'ii_CN';
    case ICELANDIC = 'is';
    case ICELANDIC_ICELAND = 'is_IS';
    case ITALIAN = 'it';
    case ITALIAN_SWITZERLAND = 'it_CH';
    case ITALIAN_ITALY = 'it_IT';
    case ITALIAN_SAN_MARINO = 'it_SM';
    case ITALIAN_VATICAN_CITY = 'it_VA';
    case JAPANESE = 'ja';
    case JAPANESE_JAPAN = 'ja_JP';
    case NGOMBA = 'jgo';
    case NGOMBA_CAMEROON = 'jgo_CM';
    case MACHAME = 'jmc';
    case MACHAME_TANZANIA = 'jmc_TZ';
    case JAVANESE = 'jv';
    case JAVANESE_INDONESIA = 'jv_ID';
    case GEORGIAN = 'ka';
    case GEORGIAN_GEORGIA = 'ka_GE';
    case KABYLE = 'kab';
    case KABYLE_ALGERIA = 'kab_DZ';
    case KAMBA = 'kam';
    case KAMBA_KENYA = 'kam_KE';
    case MAKONDE = 'kde';
    case MAKONDE_TANZANIA = 'kde_TZ';
    case KABUVERDIANU = 'kea';
    case KABUVERDIANU_CAPE_VERDE = 'kea_CV';
    case KAINGANG = 'kgp';
    case KAINGANG_BRAZIL = 'kgp_BR';
    case KOYRA_CHIINI = 'khq';
    case KOYRA_CHIINI_MALI = 'khq_ML';
    case KIKUYU = 'ki';
    case KIKUYU_KENYA = 'ki_KE';
    case KAZAKH = 'kk';
    case KAZAKH_KAZAKHSTAN = 'kk_KZ';
    case KAKO = 'kkj';
    case KAKO_CAMEROON = 'kkj_CM';
    case KALAALLISUT = 'kl';
    case KALAALLISUT_GREENLAND = 'kl_GL';
    case KALENJIN = 'kln';
    case KALENJIN_KENYA = 'kln_KE';
    case KHMER = 'km';
    case KHMER_CAMBODIA = 'km_KH';
    case KANNADA = 'kn';
    case KANNADA_INDIA = 'kn_IN';
    case KOREAN = 'ko';
    case KOREAN_CHINA = 'ko_CN';
    case KOREAN_NORTH_KOREA = 'ko_KP';
    case KOREAN_SOUTH_KOREA = 'ko_KR';
    case KONKANI = 'kok';
    case KONKANI_INDIA = 'kok_IN';
    case KASHMIRI = 'ks';
    case KASHMIRI_ARABIC = 'ks_Arab';
    case KASHMIRI_DEVANAGARI = 'ks_Deva';
    case SHAMBALA = 'ksb';
    case SHAMBALA_TANZANIA = 'ksb_TZ';
    case BAFIA = 'ksf';
    case BAFIA_CAMEROON = 'ksf_CM';
    case COLOGNIAN = 'ksh';
    case COLOGNIAN_GERMANY = 'ksh_DE';
    case KURDISH = 'ku';
    case KURDISH_TURKIYE = 'ku_TR';
    case CORNISH = 'kw';
    case CORNISH_UNITED_KINGDOM = 'kw_GB';
    case KUVI = 'kxv';
    case KUVI_DEVANAGARI = 'kxv_Deva';
    case KUVI_LATIN = 'kxv_Latn';
    case KUVI_ODIA = 'kxv_Orya';
    case KUVI_TELUGU = 'kxv_Telu';
    case KYRGYZ = 'ky';
    case KYRGYZ_KYRGYZSTAN = 'ky_KG';
    case LANGI = 'lag';
    case LANGI_TANZANIA = 'lag_TZ';
    case LUXEMBOURGISH = 'lb';
    case LUXEMBOURGISH_LUXEMBOURG = 'lb_LU';
    case GANDA = 'lg';
    case GANDA_UGANDA = 'lg_UG';
    case LIGURIAN = 'lij';
    case LIGURIAN_ITALY = 'lij_IT';
    case LAKOTA = 'lkt';
    case LAKOTA_UNITED_STATES = 'lkt_US';
    case LOMBARD = 'lmo';
    case LOMBARD_ITALY = 'lmo_IT';
    case LINGALA = 'ln';
    case LINGALA_ANGOLA = 'ln_AO';
    case LINGALA_CONGO_KINSHASA = 'ln_CD';
    case LINGALA_CENTRAL_AFRICAN_REPUBLIC = 'ln_CF';
    case LINGALA_CONGO_BRAZZAVILLE = 'ln_CG';
    case LAO = 'lo';
    case LAO_LAOS = 'lo_LA';
    case NORTHERN_LURI = 'lrc';
    case NORTHERN_LURI_IRAQ = 'lrc_IQ';
    case NORTHERN_LURI_IRAN = 'lrc_IR';
    case LITHUANIAN = 'lt';
    case LITHUANIAN_LITHUANIA = 'lt_LT';
    case LUBA_KATANGA = 'lu';
    case LUBA_KATANGA_CONGO_KINSHASA = 'lu_CD';
    case LUO = 'luo';
    case LUO_KENYA = 'luo_KE';
    case LUYIA = 'luy';
    case LUYIA_KENYA = 'luy_KE';
    case LATVIAN = 'lv';
    case LATVIAN_LATVIA = 'lv_LV';
    case MAITHILI = 'mai';
    case MAITHILI_INDIA = 'mai_IN';
    case MASAI = 'mas';
    case MASAI_KENYA = 'mas_KE';
    case MASAI_TANZANIA = 'mas_TZ';
    case MERU = 'mer';
    case MERU_KENYA = 'mer_KE';
    case MORISYEN = 'mfe';
    case MORISYEN_MAURITIUS = 'mfe_MU';
    case MALAGASY = 'mg';
    case MALAGASY_MADAGASCAR = 'mg_MG';
    case MAKHUWA_MEETTO = 'mgh';
    case MAKHUWA_MEETTO_MOZAMBIQUE = 'mgh_MZ';
    case META = 'mgo';
    case META_CAMEROON = 'mgo_CM';
    case MORI = 'mi';
    case MORI_NEW_ZEALAND = 'mi_NZ';
    case MACEDONIAN = 'mk';
    case MACEDONIAN_NORTH_MACEDONIA = 'mk_MK';
    case MALAYALAM = 'ml';
    case MALAYALAM_INDIA = 'ml_IN';
    case MONGOLIAN = 'mn';
    case MONGOLIAN_MONGOLIA = 'mn_MN';
    case MANIPURI = 'mni';
    case MANIPURI_BANGLA = 'mni_Beng';
    case MARATHI = 'mr';
    case MARATHI_INDIA = 'mr_IN';
    case MALAY = 'ms';
    case MALAY_BRUNEI = 'ms_BN';
    case MALAY_INDONESIA = 'ms_ID';
    case MALAY_MALAYSIA = 'ms_MY';
    case MALAY_SINGAPORE = 'ms_SG';
    case MALTESE = 'mt';
    case MALTESE_MALTA = 'mt_MT';
    case MUNDANG = 'mua';
    case MUNDANG_CAMEROON = 'mua_CM';
    case BURMESE = 'my';
    case BURMESE_MYANMAR_BURMA = 'my_MM';
    case MAZANDERANI = 'mzn';
    case MAZANDERANI_IRAN = 'mzn_IR';
    case NAMA = 'naq';
    case NAMA_NAMIBIA = 'naq_NA';
    case NORWEGIAN_BOKML = 'nb';
    case NORWEGIAN_BOKML_NORWAY = 'nb_NO';
    case NORWEGIAN_BOKML_SVALBARD_JAN_MAYEN = 'nb_SJ';
    case NORTH_NDEBELE = 'nd';
    case NORTH_NDEBELE_ZIMBABWE = 'nd_ZW';
    case LOW_GERMAN = 'nds';
    case LOW_GERMAN_GERMANY = 'nds_DE';
    case LOW_GERMAN_NETHERLANDS = 'nds_NL';
    case NEPALI = 'ne';
    case NEPALI_INDIA = 'ne_IN';
    case NEPALI_NEPAL = 'ne_NP';
    case DUTCH = 'nl';
    case DUTCH_ARUBA = 'nl_AW';
    case DUTCH_BELGIUM = 'nl_BE';
    case DUTCH_CARIBBEAN_NETHERLANDS = 'nl_BQ';
    case DUTCH_CURACAO = 'nl_CW';
    case DUTCH_NETHERLANDS = 'nl_NL';
    case DUTCH_SURINAME = 'nl_SR';
    case DUTCH_SINT_MAARTEN = 'nl_SX';
    case KWASIO = 'nmg';
    case KWASIO_CAMEROON = 'nmg_CM';
    case NORWEGIAN_NYNORSK = 'nn';
    case NORWEGIAN_NYNORSK_NORWAY = 'nn_NO';
    case NGIEMBOON = 'nnh';
    case NGIEMBOON_CAMEROON = 'nnh_CM';
    case NORWEGIAN = 'no';
    case N_KO = 'nqo';
    case N_KO_GUINEA = 'nqo_GN';
    case NUER = 'nus';
    case NUER_SOUTH_SUDAN = 'nus_SS';
    case NYANKOLE = 'nyn';
    case NYANKOLE_UGANDA = 'nyn_UG';
    case OCCITAN = 'oc';
    case OCCITAN_SPAIN = 'oc_ES';
    case OCCITAN_FRANCE = 'oc_FR';
    case OROMO = 'om';
    case OROMO_ETHIOPIA = 'om_ET';
    case OROMO_KENYA = 'om_KE';
    case ODIA = 'or';
    case ODIA_INDIA = 'or_IN';
    case OSSETIC = 'os';
    case OSSETIC_GEORGIA = 'os_GE';
    case OSSETIC_RUSSIA = 'os_RU';
    case PUNJABI = 'pa';
    case PUNJABI_ARABIC = 'pa_Arab';
    case PUNJABI_GURMUKHI = 'pa_Guru';
    case NIGERIAN_PIDGIN = 'pcm';
    case NIGERIAN_PIDGIN_NIGERIA = 'pcm_NG';
    case POLISH = 'pl';
    case POLISH_POLAND = 'pl_PL';
    case PRUSSIAN = 'prg';
    case PRUSSIAN_POLAND = 'prg_PL';
    case PASHTO = 'ps';
    case PASHTO_AFGHANISTAN = 'ps_AF';
    case PASHTO_PAKISTAN = 'ps_PK';
    case PORTUGUESE = 'pt';
    case PORTUGUESE_ANGOLA = 'pt_AO';
    case PORTUGUESE_BRAZIL = 'pt_BR';
    case PORTUGUESE_SWITZERLAND = 'pt_CH';
    case PORTUGUESE_CAPE_VERDE = 'pt_CV';
    case PORTUGUESE_EQUATORIAL_GUINEA = 'pt_GQ';
    case PORTUGUESE_GUINEA_BISSAU = 'pt_GW';
    case PORTUGUESE_LUXEMBOURG = 'pt_LU';
    case PORTUGUESE_MACAO_SAR_CHINA = 'pt_MO';
    case PORTUGUESE_MOZAMBIQUE = 'pt_MZ';
    case PORTUGUESE_PORTUGAL = 'pt_PT';
    case PORTUGUESE_SAO_TOME_PRINCIPE = 'pt_ST';
    case PORTUGUESE_TIMOR_LESTE = 'pt_TL';
    case QUECHUA = 'qu';
    case QUECHUA_BOLIVIA = 'qu_BO';
    case QUECHUA_ECUADOR = 'qu_EC';
    case QUECHUA_PERU = 'qu_PE';
    case RAJASTHANI = 'raj';
    case RAJASTHANI_INDIA = 'raj_IN';
    case ROMANSH = 'rm';
    case ROMANSH_SWITZERLAND = 'rm_CH';
    case RUNDI = 'rn';
    case RUNDI_BURUNDI = 'rn_BI';
    case ROMANIAN = 'ro';
    case ROMANIAN_MOLDOVA = 'ro_MD';
    case ROMANIAN_ROMANIA = 'ro_RO';
    case ROMBO = 'rof';
    case ROMBO_TANZANIA = 'rof_TZ';
    case RUSSIAN = 'ru';
    case RUSSIAN_BELARUS = 'ru_BY';
    case RUSSIAN_KYRGYZSTAN = 'ru_KG';
    case RUSSIAN_KAZAKHSTAN = 'ru_KZ';
    case RUSSIAN_MOLDOVA = 'ru_MD';
    case RUSSIAN_RUSSIA = 'ru_RU';
    case RUSSIAN_UKRAINE = 'ru_UA';
    case KINYARWANDA = 'rw';
    case KINYARWANDA_RWANDA = 'rw_RW';
    case RWA = 'rwk';
    case RWA_TANZANIA = 'rwk_TZ';
    case SANSKRIT = 'sa';
    case SANSKRIT_INDIA = 'sa_IN';
    case YAKUT = 'sah';
    case YAKUT_RUSSIA = 'sah_RU';
    case SAMBURU = 'saq';
    case SAMBURU_KENYA = 'saq_KE';
    case SANTALI = 'sat';
    case SANTALI_OL_CHIKI = 'sat_Olck';
    case SANGU = 'sbp';
    case SANGU_TANZANIA = 'sbp_TZ';
    case SARDINIAN = 'sc';
    case SARDINIAN_ITALY = 'sc_IT';
    case SINDHI = 'sd';
    case SINDHI_ARABIC = 'sd_Arab';
    case SINDHI_DEVANAGARI = 'sd_Deva';
    case NORTHERN_SAMI = 'se';
    case NORTHERN_SAMI_FINLAND = 'se_FI';
    case NORTHERN_SAMI_NORWAY = 'se_NO';
    case NORTHERN_SAMI_SWEDEN = 'se_SE';
    case SENA = 'seh';
    case SENA_MOZAMBIQUE = 'seh_MZ';
    case KOYRABORO_SENNI = 'ses';
    case KOYRABORO_SENNI_MALI = 'ses_ML';
    case SANGO = 'sg';
    case SANGO_CENTRAL_AFRICAN_REPUBLIC = 'sg_CF';
    case TACHELHIT = 'shi';
    case TACHELHIT_LATIN = 'shi_Latn';
    case TACHELHIT_TIFINAGH = 'shi_Tfng';
    case SINHALA = 'si';
    case SINHALA_SRI_LANKA = 'si_LK';
    case SLOVAK = 'sk';
    case SLOVAK_SLOVAKIA = 'sk_SK';
    case SLOVENIAN = 'sl';
    case SLOVENIAN_SLOVENIA = 'sl_SI';
    case INARI_SAMI = 'smn';
    case INARI_SAMI_FINLAND = 'smn_FI';
    case SHONA = 'sn';
    case SHONA_ZIMBABWE = 'sn_ZW';
    case SOMALI = 'so';
    case SOMALI_DJIBOUTI = 'so_DJ';
    case SOMALI_ETHIOPIA = 'so_ET';
    case SOMALI_KENYA = 'so_KE';
    case SOMALI_SOMALIA = 'so_SO';
    case ALBANIAN = 'sq';
    case ALBANIAN_ALBANIA = 'sq_AL';
    case ALBANIAN_NORTH_MACEDONIA = 'sq_MK';
    case ALBANIAN_KOSOVO = 'sq_XK';
    case SERBIAN = 'sr';
    case SERBIAN_SERBIA = 'sr_RS';
    case SERBIAN_CYRILLIC = 'sr_Cyrl';
    case SERBIAN_CYRILLIC_SERBIA = 'sr_Cyrl_RS';
    case SERBIAN_LATIN = 'sr_Latn';
    case SERBIAN_LATIN_SERBIA = 'sr_Latn_RS';
    case SUNDANESE = 'su';
    case SUNDANESE_LATIN = 'su_Latn';
    case SWEDISH = 'sv';
    case SWEDISH_ALAND_ISLANDS = 'sv_AX';
    case SWEDISH_FINLAND = 'sv_FI';
    case SWEDISH_SWEDEN = 'sv_SE';
    case SWAHILI = 'sw';
    case SWAHILI_CONGO_KINSHASA = 'sw_CD';
    case SWAHILI_KENYA = 'sw_KE';
    case SWAHILI_TANZANIA = 'sw_TZ';
    case SWAHILI_UGANDA = 'sw_UG';
    case SYRIAC = 'syr';
    case SYRIAC_IRAQ = 'syr_IQ';
    case SYRIAC_SYRIA = 'syr_SY';
    case SILESIAN = 'szl';
    case SILESIAN_POLAND = 'szl_PL';
    case TAMIL = 'ta';
    case TAMIL_INDIA = 'ta_IN';
    case TAMIL_SRI_LANKA = 'ta_LK';
    case TAMIL_MALAYSIA = 'ta_MY';
    case TAMIL_SINGAPORE = 'ta_SG';
    case TELUGU = 'te';
    case TELUGU_INDIA = 'te_IN';
    case TESO = 'teo';
    case TESO_KENYA = 'teo_KE';
    case TESO_UGANDA = 'teo_UG';
    case TAJIK = 'tg';
    case TAJIK_TAJIKISTAN = 'tg_TJ';
    case THAI = 'th';
    case THAI_THAILAND = 'th_TH';
    case TIGRINYA = 'ti';
    case TIGRINYA_ERITREA = 'ti_ER';
    case TIGRINYA_ETHIOPIA = 'ti_ET';
    case TURKMEN = 'tk';
    case TURKMEN_TURKMENISTAN = 'tk_TM';
    case TONGAN = 'to';
    case TONGAN_TONGA = 'to_TO';
    case TOKI_PONA = 'tok';
    case TURKISH = 'tr';
    case TURKISH_CYPRUS = 'tr_CY';
    case TURKISH_TURKIYE = 'tr_TR';
    case TATAR = 'tt';
    case TATAR_RUSSIA = 'tt_RU';
    case TASAWAQ = 'twq';
    case TASAWAQ_NIGER = 'twq_NE';
    case CENTRAL_ATLAS_TAMAZIGHT = 'tzm';
    case CENTRAL_ATLAS_TAMAZIGHT_MOROCCO = 'tzm_MA';
    case UYGHUR = 'ug';
    case UYGHUR_CHINA = 'ug_CN';
    case UKRAINIAN = 'uk';
    case UKRAINIAN_UKRAINE = 'uk_UA';
    case URDU = 'ur';
    case URDU_INDIA = 'ur_IN';
    case URDU_PAKISTAN = 'ur_PK';
    case UZBEK = 'uz';
    case UZBEK_ARABIC = 'uz_Arab';
    case UZBEK_CYRILLIC = 'uz_Cyrl';
    case UZBEK_LATIN = 'uz_Latn';
    case VAI = 'vai';
    case VAI_LATIN = 'vai_Latn';
    case VAI_VAI = 'vai_Vaii';
    case VENETIAN = 'vec';
    case VENETIAN_ITALY = 'vec_IT';
    case VIETNAMESE = 'vi';
    case VIETNAMESE_VIETNAM = 'vi_VN';
    case MAKHUWA = 'vmw';
    case MAKHUWA_MOZAMBIQUE = 'vmw_MZ';
    case VUNJO = 'vun';
    case VUNJO_TANZANIA = 'vun_TZ';
    case WALSER = 'wae';
    case WALSER_SWITZERLAND = 'wae_CH';
    case WOLOF = 'wo';
    case WOLOF_SENEGAL = 'wo_SN';
    case XHOSA = 'xh';
    case XHOSA_SOUTH_AFRICA = 'xh_ZA';
    case KANGRI = 'xnr';
    case KANGRI_INDIA = 'xnr_IN';
    case SOGA = 'xog';
    case SOGA_UGANDA = 'xog_UG';
    case YANGBEN = 'yav';
    case YANGBEN_CAMEROON = 'yav_CM';
    case YIDDISH = 'yi';
    case YIDDISH_UKRAINE = 'yi_UA';
    case YORUBA = 'yo';
    case YORUBA_BENIN = 'yo_BJ';
    case YORUBA_NIGERIA = 'yo_NG';
    case NHEENGATU = 'yrl';
    case NHEENGATU_BRAZIL = 'yrl_BR';
    case NHEENGATU_COLOMBIA = 'yrl_CO';
    case NHEENGATU_VENEZUELA = 'yrl_VE';
    case CANTONESE = 'yue';
    case CANTONESE_SIMPLIFIED = 'yue_Hans';
    case CANTONESE_TRADITIONAL = 'yue_Hant';
    case ZHUANG = 'za';
    case ZHUANG_CHINA = 'za_CN';
    case STANDARD_MOROCCAN_TAMAZIGHT = 'zgh';
    case STANDARD_MOROCCAN_TAMAZIGHT_MOROCCO = 'zgh_MA';
    case CHINESE = 'zh';
    case CHINESE_SIMPLIFIED = 'zh_Hans';
    case CHINESE_TRADITIONAL = 'zh_Hant';
    case ZULU = 'zu';
    case ZULU_SOUTH_AFRICA = 'zu_ZA';

    /**
     * Retrieves the system's default locale from the PHP environment settings.
     *
     * This method returns the locale configured via `intl.default_locale`. Should the PHP environment lack a specific
     * locale configuration or if the configured locale is unsupported, it defaults to `self::English` representing
     * the English language.
     *
     * The choice of English as the fallback is motivated by its global comprehension, role as a base language in
     * technology and international communication, predominance in technical documentation and support resources,
     * and its historical precedence in the tech industry. These factors ensure the fallback locale is both broadly
     * accessible and consistent with international standards.
     *
     * @return self The default locale as an enum instance, sourced from PHP settings or `self::English` as the fallback.
     *
     * @see https://www.php.net/manual/en/locale.getdefault.php
     */
    public static function default(): self
    {
        $full_locale = NativeLocale::getDefault();

        if (! $full_locale) {
            // Fallback to English if no locale is set or supported.
            return self::ENGLISH;
        }

        $language = (string) NativeLocale::getPrimaryLanguage($full_locale);
        $script = (string) NativeLocale::getScript($full_locale);
        $region = (string) NativeLocale::getRegion($full_locale);

        $locale = to_lower_case($language);

        if ($script) {
            $locale .= '_' . upper_first($script);
        }

        if ($region) {
            $locale .= '_' . to_upper_case($region);
        }

        // Attempt to match the system-configured locale with a supported enum instance,
        // defaulting to English if a precise match is unavailable.
        return self::tryFrom($locale) ?? self::tryFrom($language) ?? self::ENGLISH;
    }

    /**
     * Gets a human-readable name for the locale, suitable for display.
     *
     * @param Locale|null $locale The locale for which to get the name. Defaults to the current locale if not specified.
     */
    public function getDisplayName(?Locale $locale = null): string
    {
        return NativeLocale::getDisplayName($this->value, $locale->value ?? $this->value);
    }

    /**
     * Gets the language code part of the locale.
     */
    public function getLanguage(): string
    {
        return NativeLocale::getPrimaryLanguage($this->value);
    }

    /**
     * Gets the display name of the language for the locale.
     *
     * @param Locale|null $locale The locale for which to get the language name. Defaults to the current locale if not specified.
     */
    public function getDisplayLanguage(?Locale $locale = null): string
    {
        return NativeLocale::getDisplayLanguage($this->value, $locale->value ?? $this->value);
    }

    /**
     * Checks if the locale has a script specified.
     *
     * @return bool True if the locale has a script, false otherwise.
     */
    public function hasScript(): bool
    {
        return $this->getScript() !== null;
    }

    /**
     * Gets the script of the locale.
     *
     * @return non-empty-string|null The script of the locale, or null if not applicable.
     */
    public function getScript(): ?string
    {
        $script = NativeLocale::getScript($this->value);

        if (! $script) {
            return null;
        }

        return $script;
    }

    /**
     * Checks if the locale has a region specified.
     *
     * @return bool True if the locale has a region, false otherwise.
     */
    public function hasRegion(): bool
    {
        return $this->getRegion() !== null;
    }

    /**
     * Get the display name of the region for the locale.
     *
     * @param Locale|null $locale The locale for which to get the region name. Defaults to the current locale if not specified.
     *
     * @return non-empty-string|null The display name of the region, or null if not applicable.
     */
    public function getDisplayRegion(?Locale $locale = null): ?string
    {
        $displayRegion = NativeLocale::getDisplayRegion($this->value, $locale->value ?? $this->value);

        if (! $displayRegion) {
            return null;
        }

        return $displayRegion;
    }

    /**
     * Gets the alpha-2 country code part of the locale, if present.
     *
     * @return non-empty-string|null The alpha-2 country code, or null if not present.
     */
    public function getRegion(): ?string
    {
        $region = NativeLocale::getRegion($this->value);

        if (! $region) {
            return null;
        }

        return $region;
    }
}
