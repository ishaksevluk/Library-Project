-----------------------------------------------------------------------------
--
--  Logical unit: F8libBorrowRecords
--  Component:    F8LIB
--
--  IFS Developer Studio Template Version 3.0
--
--  Date    Sign    History
--  ------  ------  ---------------------------------------------------------
-----------------------------------------------------------------------------

layer Core;

-------------------- PUBLIC DECLARATIONS ------------------------------------


-------------------- PRIVATE DECLARATIONS -----------------------------------


-------------------- LU SPECIFIC IMPLEMENTATION METHODS ---------------------

@Override
PROCEDURE Check_Insert___ (
   newrec_ IN OUT f8lib_borrow_records_tab%ROWTYPE,
   indrec_ IN OUT Indicator_Rec,
   attr_   IN OUT VARCHAR2 )
IS
BEGIN
   --(+)20250725 ishak (basla)
   newrec_.borrow_record_id := 0;
   --(+)20250725 ishak (bitir)
   super(newrec_, indrec_, attr_);
END Check_Insert___;

@Override
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_borrow_records_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
   CURSOR get_max_borrow_record_id IS 
      SELECT MAX(borrow_record_id)
      FROM f8lib_borrow_records_tab;
   max_id_ NUMBER;

   active_borrows_count NUMBER;
BEGIN
 
   SELECT COUNT(*)
   INTO active_borrows_count
   FROM f8lib_borrow_records_tab
   WHERE user_id = newrec_.user_id
     AND return_date IS NULL;  

   IF active_borrows_count >= 2 THEN
          ERROR_SYS.Record_General('lu_name_','NO_BOOK_FOUND: Bir kullanici en fazla iki kitap odunc alabilir!',NULL,NULL);
   END IF;

   --Add pre-processing code here
   OPEN get_max_borrow_record_id ;
   FETCH get_max_borrow_record_id  INTO max_id_;
   CLOSE get_max_borrow_record_id ;
   newrec_.borrow_record_id := NVL(max_id_,0)+1;
   Client_SYS.Add_To_Attr('BORROW_RECORD_ID',   newrec_.borrow_record_id ,       attr_);
   Client_SYS.Add_To_Attr('BORROW_DATE',   SYSDATE,       attr_);
   Client_SYS.Add_To_Attr('RETURN_DATE',   SYSDATE,       attr_);
   super(objid_, objversion_, newrec_, attr_);
   --Add post-processing code here
END Insert___;

-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------


-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------


-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------


PROCEDURE Add_Borrow_Records(book_id_ NUMBER,
                          user_id_ NUMBER)
IS
   my_attr_          VARCHAR2(3200);
   my_objid_         VARCHAR2(2000);
   my_objversion_    VARCHAR2(2000);
   my_info_          VARCHAR2(32000);
   CURSOR get_obj_values IS 
   SELECT objid,objversion
   FROM f8lib_books
   WHERE book_id = book_id_;
BEGIN
   
   Client_SYS.Clear_Attr(my_attr_);
   Client_SYS.Add_To_Attr('USER_ID',user_id_,my_attr_);
   Client_SYS.Add_To_Attr('BOOK_ID',book_id_,my_attr_);
   Client_SYS.Add_To_Attr('BORROW_RECORD_ID',0,my_attr_);
   F8lib_Borrow_Records_API.New__(my_info_, my_objid_, my_objversion_, my_attr_, 'DO');
   my_attr_          :=NULL;
   my_objid_         :=NULL;
   my_objversion_    :=NULL;
   my_info_          :=NULL;
   OPEN get_obj_values;
   FETCH get_obj_values INTO my_objid_,my_objversion_;
   CLOSE get_obj_values;
   F8lib_Books_API.Borrow__(my_info_,my_objid_,my_objversion_,my_attr_,'DO');
   
END Add_Borrow_Records;