<?php


use Phinx\Migration\AbstractMigration;

class AddEventInterval extends AbstractMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         $table = $this->table('event');
         $table->addColumn('interval', 'string')
               ->save();
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         $table = $this->table('event');
         $table->RemoveColumn('interval')
               ->save();
     }
}
