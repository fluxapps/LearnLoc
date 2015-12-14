# ILIAS Learning Locations Plugin

With this Plugin you can use the ILIAS Learning Location App, available at:  
- [Google-PlayStore](https://play.google.com/store/apps/details?id=ch.studerraimann.ilias.learnloc)  
- [Apple AppStore](#) Coming Soon  

Navigate with Augmented Reality to Learning Locations and open ILIAS Modules associated with this Point of Learning (opened in Browser).

###Installation
Start at your ILIAS root directory  
```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject  
cd Customizing/global/plugins/Services/Repository/RepositoryObject
git clone https://github.com/studer-raimann/LiveVoting.git  
chown -R www-data:www-data LiveVoting #ensure that the user of your Webserver has full access to this directory
```  
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.  

###Contact
studer + raimann ag  
Waldeggstrasse 72  
3097 Liebefeld  
Switzerland  

info@studer-raimann.ch  
www.studer-raimann.ch