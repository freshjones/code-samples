<?xml version="1.0" encoding="UTF-8" ?>

<project name="Migrate" default="run">

  <target name="init" description="Load main settings">
    <property file="migrate.properties" />
  </target>
  
  <target name="sync-files" description="Sync all files with remote server" depends="init">
    <echo message="${line.separator}Syncing ${environment} files...${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}" />
    <if>
     <equals arg1="${environment}" arg2="production" />
     <then>
      <exec command="${prog.rsync} -va --exclude 'fluorosrch' ${sync.source.projectdir} ${sync.remote.user}@${sync.remote.host}:${sync.destination.projectdir} &gt;&gt; ${build.logfile}" />
     </then>
     <else>
       <exec command="${prog.rsync} -va --exclude 'fluorosrch' ${sync.source.projectdir} ${sync.destination.projectdir} &gt;&gt; ${build.logfile}" />
     </else>
    </if>
      <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="sync-fluorosrch-files" description="Sync all fluorosrch files with remote server" depends="init">
    
    <if>
    
     <equals arg1="${environment}" arg2="production" />
     <then>
       <echo message="${line.separator}Syncing production fluorosrch files...${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}" />
       <exec command="${prog.rsync} -va ${sync.fluorosrch.source} ${sync.remote.user}@${sync.remote.host}:${sync.fluorosrch.production.destination} &gt;&gt; ${build.logfile}" />
     </then>
    
     <elseif>
      <equals arg1="${environment}" arg2="staging" />
      <then>
       <echo message="${line.separator}Syncing staging fluorosrch files...${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}" />
       <exec command="${prog.rsync} -va ${sync.fluorosrch.source} ${sync.fluorosrch.staging.destination} &gt;&gt; ${build.logfile}" />
      </then>
     </elseif>
    
     <else>
       <echo message="${line.separator}Syncing development fluorosrch files...${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}" />
       <exec command="${prog.rsync} -va ${sync.fluorosrch.source} ${sync.fluorosrch.development.destination} &gt;&gt; ${build.logfile}" />
     </else>
     
    </if>

    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-mi-spectra" description="run drupal spectra migration">
    <echo message="${line.separator}Migrating Spectra Files...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=spectra --track-unprocessed &gt;&gt; ${build.logfile}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-mi-taxonomy" description="run drupal taxonomy migration" depends="init" >
    <echo message="${line.separator}Migrating Taxonomy...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=taxonomy --track-unprocessed &gt;&gt; ${build.logfile}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-mi-fluorochrome" description="run drupal fluorochrome migration" depends="init" >
    <echo message="${line.separator}Migrating Fluorochromes...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=fluorochrome --track-unprocessed &gt;&gt; ${build.logfile}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-mi-accessories" description="run drupal accessories migration" depends="init" >
    <echo message="${line.separator}Migrating Accessories...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=accessories --track-unprocessed &gt;&gt; ${build.logfile}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-mi-custom" description="run drupal custom migration" depends="init" >
    <echo message="${line.separator}Migrating Custom...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=custom --track-unprocessed &gt;&gt; ${build.logfile}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>

  <target name="run-drush-mi-parts" description="run drupal parts migration" depends="init" >
    <echo message="${line.separator}Migrating Parts...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=parts --track-unprocessed &gt;&gt; ${build.logfile}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-mi-sets" description="run drupal sets migration" depends="init" >
    <echo message="${line.separator}Migrating Sets...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=sets --track-unprocessed &gt;&gt; ${build.logfile}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target> 
  
  <target name="run-drush-mi-reclaimedcubes" description="run drupal reclaimedcubes migration" depends="init" >
    <echo message="${line.separator}Migrating Reclaimed Cubes...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-import --group=reclaimedcubes --track-unprocessed  &gt;&gt; ${build.logfile}" />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-mi" description="run drupal import migration" depends="init, run-drush-mi-spectra, run-drush-mi-taxonomy, run-drush-mi-fluorochrome, run-drush-mi-accessories, run-drush-mi-custom, run-drush-mi-parts, run-drush-mi-sets, run-drush-mi-reclaimedcubes" >
    <echo message="${line.separator}Drush Migration Import Complete${line.separator}" file="${build.logfile}" append="${build.append}" />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run-drush-purge" description="run drupal import migration" depends="init" >
    <echo message="${line.separator}Purging any deleted records...${line.separator}" file="${build.logfile}" append="${build.append}" />
    <exec command="${prog.drush} @chroma-${environment} migrate-rollback --all --unprocessed --do-not-reset-highwater &gt;&gt; ${build.logfile}" />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>

  <target name="run-migration" description="run drupal migration" depends="init, sync-files, sync-fluorosrch-files, run-drush-mi, run-drush-purge" >
    <echo message="File Sync, Migration Imports and Purges Completed.${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}" />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="migrate" description="run drupal migrations" depends="run-migration"  >
    <echo message="${line.separator}Migrating..." file="${build.logfile}" append="${build.append}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
      <sleepcheck amount="${build.sleep}" />
  </target>
  
  <target name="run" depends="migrate">
    <echo message="[SUCCESS]${line.separator}${line.separator}" file="${build.logfile}" append="${build.append}"  />
    <taskdef name="sleepcheck" classname="tasks.sleepTask" />
        <sleepcheck amount="15" />
  </target>
  
</project>
