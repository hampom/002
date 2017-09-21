<?php


use Phinx\Migration\AbstractMigration;

class AddCalendarTable extends AbstractMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         $table = $this->table('calendar');
         $table->addColumn('calendar_id', 'string')
               ->addColumn('title', 'string')
               ->addColumn('description', 'text')
               ->save();
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         $this->dropTable('calendar');
     }
}
