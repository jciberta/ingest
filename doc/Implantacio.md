# InGest

## Implantació

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
chmod 755 * -R
nano ingest/src/Config.php
```

Actualitzem l'enllaç simbòlic
```
cd ..
rm ingest
ln -s InGest-v$VERSIO/ingest/src ingest
```

### Altres

How do I make Git ignore file mode (chmod) changes?
```
git config core.fileMode false
```
