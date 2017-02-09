# ILIAS Learning Locations Plugin

With this Plugin you can use the ILIAS Learning Location App, available at:  
- [Google-PlayStore](https://play.google.com/store/apps/details?id=ch.studerraimann.ilias.learnloc)  
- [Apple AppStore](https://itunes.apple.com/ch/app/ilias-lernorte/id1066335052)

Navigate with Augmented Reality to Learning Locations and open ILIAS Modules associated with this Point of Learning (opened in Browser).

### Installation
Start at your ILIAS root directory  
```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject  
cd Customizing/global/plugins/Services/Repository/RepositoryObject
git clone https://github.com/studer-raimann/LearnLoc.git  
chown -R www-data:www-data LearnLoc #ensure that the user of your Webserver has full access to this directory
```  
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.  

### Hinweis Plugin-Patenschaft
Grundsätzlich veröffentlichen wir unsere Plugins (Extensions, Add-Ons), weil wir sie für alle Community-Mitglieder zugänglich machen möchten. Auch diese Extension wird der ILIAS Community durch die studer + raimann ag als open source zur Verfügung gestellt. Diese Plugin hat noch keinen Plugin-Paten. Das bedeutet, dass die studer + raimann ag etwaige Fehlerbehebungen, Supportanfragen oder die Release-Pflege lediglich für Kunden mit entsprechendem Hosting-/Wartungsvertrag leistet. Falls Sie nicht zu unseren Hosting-Kunden gehören, bitten wir Sie um Verständnis, dass wir leider weder kostenlosen Support noch Release-Pflege für Sie garantieren können.

Sind Sie interessiert an einer Plugin-Patenschaft (https://studer-raimann.ch/produkte/ilias-plugins/plugin-patenschaften/ ) Rufen Sie uns an oder senden Sie uns eine E-Mail.

### Contact
studer + raimann ag  
Waldeggstrasse 72  
3097 Liebefeld  
Switzerland  

info@studer-raimann.ch  
www.studer-raimann.ch