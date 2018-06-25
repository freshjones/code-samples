<?xml version="1.0" encoding="UTF-8"?>

<project name="Save" default="run">

  <target name="init" description="Load main settings">
    <property name="archive" value="${environment}_${build.start}" />
    <property file="save.properties" />
    
    <if>
      <isset property="isrelease" />
      <then>
        <echo message="We have a release setting already" />
      </then>
      <else>
        <property name="isrelease" value="false" />
      </else>
    </if>
    
  </target>
  
  <target name="create-build-dir">
        <echo message="Creating temporary build directory..." file="${build.logfile}" append="${build.append}"  />
        <mkdir dir="./tmp" />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="remove-build-dir">
        <echo message="Removing temporary build directory..." file="${build.logfile}" append="${build.append}"  />
        <delete dir="./tmp" includeemptydirs="true" />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  
  <target name="sync-site-files">
        <echo message="Syncing all site files..." file="${build.logfile}" append="${build.append}"  />
    
    <exec command="${prog.drush} --yes
        rsync
        --mode=rulz
        --exclude=styles 
        --exclude=advagg_js 
        --exclude=advagg_css 
        --exclude=js 
        --exclude=css 
        @chroma-${environment}:%files/
        ${rsync.local.dir}/${environment}/files/site_files/
        &gt;&gt; ${build.logfile}" 
        logOutput="true"
        />
      
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="sync-private-files">
        <echo message="Syncing all private files..." file="${build.logfile}" append="${build.append}"  />
    
    <exec command="${prog.drush} --yes 
        rsync
        --mode=rulz
        --exclude=styles 
        --exclude=advagg_js 
        --exclude=advagg_css 
        --exclude=js 
        --exclude=css 
        @chroma-${environment}:%private/
        ${rsync.local.dir}/${environment}/files/private_files/
        &gt;&gt; ${build.logfile}" 
        logOutput="true"
        />
        
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="copy-files-to-tmp">
        <echo message="Copying ${environment} files to tmp..." file="${build.logfile}" append="${build.append}"  />
  
        <copy todir="./tmp/files" overwrite="true">
          <fileset dir="${rsync.local.dir}/${environment}/files" />
        </copy>
        
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="archive-files" depends="init, sync-site-files, sync-private-files, copy-files-to-tmp" />
  
  <target name="create-mysql-dump" depends="init">
        <echo message="Creating a MySQL Database Dump..." file="${build.logfile}" append="${build.append}"  />
        <exec command="${prog.drush} @chroma-${environment} sql-dump --gzip &gt; ${rsync.local.dir}/${environment}/databases/database.sql.gz"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="copy-database-to-tmp">
        <echo message="Copying database.sql.gz to tmp/database..." file="${build.logfile}" append="${build.append}"  />
  
      <mkdir dir="/tmp/database" />
      
        <copy file="${rsync.local.dir}/${environment}/databases/database.sql.gz" todir="./tmp/database" overwrite="true" />
        
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="archive-database" depends="init, create-mysql-dump, copy-database-to-tmp" />
  
  
  <target name="create-archive-file" >
        <echo message="Creating the archive file ${archive}.tar.gz..." file="${build.logfile}" append="${build.append}"  />
        <tar destfile="${archive}.tar.gz" basedir="./tmp" compression="gzip"/>
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="move-archive-file" >
        <echo message="Moving the archive file ${archive}.tar.gz to archive directory..." file="${build.logfile}" append="${build.append}"  />
    
    <move file="${archive}.tar.gz" todir="${rsync.local.dir}/archives" />

    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  <target name="archive-build" depends="create-archive-file, move-archive-file" />
  
  
  <target name="record-archive" >
        <echo message="Recording the Archive..." file="${build.logfile}" append="${build.append}"  />
    
    <taskdef name="recordarchive" classname="tasks.recordArchiveTask" />
      <recordarchive 
        archive="${archive}.tar.gz"
        label="${label}"
        environment="${environment}"
        isrelease="${isrelease}"
      />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
        <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
  </target>
  
  
  <target name="save" depends="init, create-build-dir, archive-files, archive-database, archive-build, record-archive, remove-build-dir" />
  
  <target name="run" depends="save">
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="${save.sleep}" />
  </target>
  
</project>
