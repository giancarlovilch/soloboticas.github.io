# Code...

### PowerShell

```powershell
cd ..
cd carpeta
dir
mkdir new-folder-name
md  new-folder-name
ni new-file-name
move file.html folder
mv file.html folder
pwd // ver la ruta actual
cls
clear
```

### Git

```powershell
git help
git version
git init
git add . // agregar todos los cambios
git status -s // ver que es lo que pasa
git log --oneline
git commit -m "commit-message"
git config user.name
git config user.email
git remote "https://github.com/giancarlovilch/Project004.git"
git remote -v // revisar el url
.gitignore // adjunta todos los archivos ocultos
git checkout bc238ef // revisa todos los cambios hechos en el pasado
git reset 5cd9b87 // revisas el commit pero eliminando los otros commit
git reset --hard 5cd9b87 // revisa los commits pero eliminando el resto
git reflog // revisa todo el historial de commits
git revert 5cd9b87 // elimina un determinado commit
git branch // revisa en que rama estoy
git branch branch-name // crea una rama
git checkout branch-name // selecciona una rama para trabajar
git log --oneline --graph // ver el mapa de todas las ramas
git push --set-upstream origin branch-name
git merge branch-newname // junta todos las ramas
git tag versionAlpha -m "0.0.1" // asigna un tag de manera interna
git push --tags // sube el tags al git
git pull // aplica todos los cambios en git a nuestra pc
git brach -m master new-branch-name // cambiar de nombre a la rama
git config --global init.defaultBranch main // cambia de manera global la rama
```

### Create a New Git Web

```powershell
echo "# giancarlovilch-github.com" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/giancarlovilch/giancarlovilch-github.com.git
git push -u origin main

git remote add origin https://github.com/giancarlovilch/giancarlovilch-github.com.git
git branch -M main
git push -u origin main
```

### HTML

```html
<a href=""></a>
/*If you wanna hide something, just do it*/

```

### CMD

```powershell
net user nameuser "sirve para ver informacion del usuario"
arp -a "sirve para "
```

