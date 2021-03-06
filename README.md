# Hakkında
Wordpress tema ve eklenti yapımı ile uğraşmayı düşünen geliştiriciler için başlangıç düzeyince uzak sunucu kontrollü lisans sistemi hazırladım umarım işinizi görür. Projenizde başarılar dilerim, takıldığınız nokta olursa uckancagri@gmail.com adresinden bana ulaşabilirsiniz.

## Çalışma Prensibi
Uzak bir sunucuda tutulan json dosyası içerisinde lisanslı domainlerin bilgisi yer alır. Yazdığım wordpress görevi sayesinde bu uzak sunucuya haftalık olarak erişim sağlanır ve lisans kontrolü yapılır.

## Kurulum
Tema veya eklentinizde bir ```license.php``` dosyası oluşturun ve içerisine [wp-lisans-sistemi/license.php](https://github.com/cagriuckan/wp-lisans-sistemi/blob/main/license.php) sayfasındaki kodları ekleyin.

Uzak sunucuda  ```server.json``` dosyası oluşturun, örnek datayı aşağıda görüntüleyebilirsiniz. Geçerli status değişkeni olarak ```active,deactive ve expired``` kullanabilirsiniz.
```
{
    "domains": {
        "localhost": {
            "status": "active"
        },
        "google.com": {
            "status": "expired"
        }
    }
}
```

Daha sonra ```functions.php``` dosyasına aşağıdaki kodu ekleyin ve link kısmına uzak sunucu linkinizi ekleyin.
```
require get_template_directory() . '/license.php';
$licensing = new Kan_Licensing_System(
	'http://localhost/wordpress/server.json'
);
```
