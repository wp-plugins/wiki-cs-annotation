=== Wiki CS Annotation ===
Contributors: William Aprilius
Tags: auto tags, auto tagging, auto link, entity annotation, suggestion, computer science, wikipedia
Requires at least: 4.1
Tested up to: 4.1.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin performs entity annotation by giving tag suggestions and creating links to Wikipedia.

== Description ==
Wiki CS Annotation is a plugin which performs entity annotation in computer science domain automatically.
This is done by suggesting tags based on the text which you want to post and creating link automatically on words or
phrases (anchor text) associate with the selected tags to Wikipedia pages.
This plugin uses Wikipedia bahasa Indonesia as source of knowledge.
The analyzed Wikipedia articles are a set of articles which are in the computer science category structure [1]. The result of this analysis is a XML file named xml_ilmu_komputer_20150416_182156.xml which can be found in the xml folder.

Wiki CS Annotation merupakan sebuah plugin yang melakukan anotasi entitas di bidang ilmu komputer secara otomatis.
Hal ini dilakukan dengan memberi saran tag berdasarkan teks yang ingin di-posting dan membuat link secara otomatis pada
kata atau frasa (teks anchor) yang bersesuaian dengan tag yang dipilih ke halaman Wikipedia terkait. 
Plugin ini menggunakan Wikipedia bahasa Indonesia sebagai sumber pengetahuan.
Artikel Wikipedia yang dianalisis merupakan himpunan artikel yang berada pada struktur kategori ilmu komputer [1]. Hasil analisis berupa sebuah berkas XML bernama xml_ilmu_komputer_20150416_182156.xml yang dapat ditemukan di dalam folder xml.

*	[1] http://id.wikipedia.org/wiki/Kategori:Ilmu_komputer

== Installation ==
1. Install and activate the plugin.
2. Import xml file (Settings -> Wiki CS Annotation). The XML file (xml_ilmu_komputer_20150416_182156.xml) can be found in xml folder.

== Frequently Asked Questions ==

= Does this plugin only work for computer science domain? - Apakah plugin ini hanya bekerja untuk bidang ilmu komputer? =

* Yes, this plugin only works for computer science domain currently. However, support for the other domain has become a plan in the future development.
* Ya, plugin ini hanya bekerja untuk bidang ilmu komputer saat ini. Namun, dukungan untuk bidang lainnya telah menjadi rencana pengembangan di masa mendatang.

= Does this plugin can only be used for bahasa Indonesia article? - Apakah plugin ini hanya dapat digunakan untuk artikel berbahasa Indonesia? =

* Yes, this plugin can only be used for bahasa Indonesia article.
* Ya, plugin ini hanya digunakan untuk artikel berbahasa Indonesia.

= I don't get the tag suggestions. - Saya tidak mendapatkan saran tag. =

* You can set smaller value of ρNA to get tag suggestions by using slider.
Smaller value of ρNA means there are more tag suggestions.
* Anda dapat memperkecil nilai ρNA untuk mendapatkan saran tag dengan menggunakan slider.
Nilai ρNA yang lebih kecil berarti terdapat lebih banyak saran tag.

= I've already set the value of ρNA to zero, I still don't get tag suggestions. - Saya telah mengatur nilai ρNA ke nol, saya masih tidak mendapat saran tag. =

* This can be occurred if the implemented algorithm judges that the entire words or phrases (in computer science domain) 
in the text which you want to post doesn't have semantic relation one to another. You can add other words or phrases in 
computer science domain which expected to have closer semantic relationship.
* Hal ini dapat terjadi jika algoritma yang diimplementasi menilai bahwa seluruh kata atau frasa bidang ilmu 
komputer dalam teks yang ingin di-posting tidak memiliki keterkaitan secara semantik satu sama lain. Anda dapat menambah kata 
atau frasa bidang ilmu komputer lainnya yang diperkirakan memiliki keterkaitan semantik lebih erat.

= I can't add my own tags. - Saya tidak dapat menambah tag buatan saya sendiri. =

* This plugin focuses on consistency between selected tags and resulted link, therefore you aren't allowed to add your own tags.
If you want to add your own tags, you can simply deactivate this plugin temporary, then add tags by using widget provided by Wordpress.
* Plugin ini berfokus pada konsistensi antara tag yang dipilih dan link yang dihasilkan, sehingga Anda tidak dapat 
menambah tag buatan Anda sendiri. Jika Anda ingin menambah tag buatan Anda sendiri, Anda dapat menonaktifkan plugin ini
untuk sementara, kemudian menambah tag dengan menggunakan widget yang disediakan oleh Wordpress.

== Screenshots ==

1. Example of tag suggestions which is given when the value of ρNA is 0.1 and selection of a tag suggestion to become post's tag. Contoh dari saran tag yang diberikan saat ρNA bernilai 0.1 dan pemilihan sebuah saran tag untuk menjadi tag dari post.
2. Anchor dictionary import facility. Fasilitas impor anchor dictionary.

== Changelog ==

= 1.0 =
* Initial version.
