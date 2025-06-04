```
Check integrity first to see corrupt index : 

git fsck --full

rm -f .git/objects/pack/{index_corrompu}.idx
rm -f .git/objects/pack/{index_corrompu}.pack

git gc --prune=now --aggressive

git repack -a -d --window=250 --depth=250

then check integrity again to see if there's any error : 

git fsck --full
```