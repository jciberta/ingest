# InGest

## Implantació

### Estructura

Dues carpetes principals:

  * Codi: /var/www/html
  * Dades: /var/www/ingest-data

### Crea versió

```
git tag <nom>
git push origin <nom>
```

### Nova versió

```
VERSIO=0.1
cd /var/www/html
mkdir InGest-v$VERSIO
cd InGest-v$VERSIO
git clone https://github.com/jciberta/ingest.git
chown www-data:www-data -R *
nano ingest/src/Config.php
```

Actualitzem l'enllaç simbòlic:
```
cd ..
rm ingest
ln -s InGest-v$VERSIO/ingest/src ingest
```

Per les imatges:
```
cd /var/www/html/ingest/img
ln -s /var/www/ingest-data/pix pix
```

### Altres

How do I make Git ignore file mode (chmod) changes?
```
git config core.fileMode false
```

Per fer les fotos quadrades i amb resolució 100x100:
```
# Linux
for fitxer in *.jpg; do convert -define jpeg:size=200x200 $fitxer -thumbnail 100x100^ -gravity center -extent 100x100 $fitxer; done

# Windows (no acaba d'anar)
for %i in (dir *.jpg) do magick -define jpeg:size=200x200 %i -thumbnail 100x100^ -gravity center -extent 100x100 %i
```

