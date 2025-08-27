-----------------------------------------------------------------------------
--
--  Logical unit: F8libReservations
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
PROCEDURE Insert___ (
   objid_      OUT    VARCHAR2,
   objversion_ OUT    VARCHAR2,
   newrec_     IN OUT f8lib_reservations_tab%ROWTYPE,
   attr_       IN OUT VARCHAR2 )
IS
   CURSOR get_max_reservation_id IS 
      SELECT MAX(reservation_id) FROM f8lib_reservations_tab;
   max_id_ NUMBER;
   CURSOR get_books_count IS 
      SELECT COUNT(*)
      FROM f8lib_books
      WHERE book_name = newrec_.book_name
      AND author_id = newrec_.author_id
      AND objstate!= 'NotAvailable';
      
   CURSOR  get_reservation_count IS 
      SELECT COUNT(*)
      FROM f8lib_reservations
      WHERE book_name = newrec_.book_name
      AND author_id = newrec_.author_id
      AND objstate = 'Reserved';
   
  
   count_books_ NUMBER;
   count_reservations_ NUMBER;
   count_user_reservation_ NUMBER;
BEGIN
   OPEN get_books_count;
   FETCH get_books_count INTO count_books_;
   CLOSE get_books_count;
   IF count_books_ <=0 THEN 
     ERROR_SYS.Record_General('lu_name_','NO_BOOK_FOUND: Bu kitap sistemde kayitli degildir veya hic kopyasi yok.',NULL,NULL);

   END IF;

   OPEN get_reservation_count;
   FETCH get_reservation_count INTO count_reservations_;
   CLOSE get_reservation_count;
   
   IF count_reservations_ = count_books_ THEN 
      ERROR_SYS.Record_General('lu_name_','ALL_RESERVED: Bu kitabin tum kopyalari rezerve edilmistir.', NULL, NULL);

   END IF;
   SELECT COUNT(*)
     INTO count_user_reservation_
     FROM f8lib_reservations_tab
    WHERE book_name = newrec_.book_name
      AND author_id = newrec_.author_id
      AND user_id = newrec_.user_id
      AND rowstate = 'Reserved';

   IF count_user_reservation_ > 0 THEN
      ERROR_SYS.Record_General('lu_name_','ALL_RESERVED: Bu kitabin icin rezervasyona sahipsiniz.', NULL, NULL);
      RETURN;
   END IF;

   OPEN get_max_reservation_id;
   FETCH get_max_reservation_id INTO max_id_;
   CLOSE get_max_reservation_id;

   newrec_.reservation_id := NVL(max_id_, 0) + 1;
   newrec_.rowstate := 'Reserved';
   

   
   Client_SYS.Add_To_Attr('RESERVATION_ID', newrec_.reservation_id, attr_);
   Client_SYS.Add_To_Attr('REQUEST_DATE', SYSDATE, attr_);
   Client_SYS.Add_To_Attr('NOTIFICATION', SYSDATE, attr_);
   

   super(objid_, objversion_, newrec_, attr_);
    

END Insert___;






PROCEDURE Do_Cancel___ (
   rec_  IN OUT f8lib_reservations_tab%ROWTYPE,
   attr_ IN OUT VARCHAR2 )
IS
BEGIN
   NULL;
END Do_Cancel___;


PROCEDURE Do_Complete___ (
   rec_  IN OUT f8lib_reservations_tab%ROWTYPE,
   attr_ IN OUT VARCHAR2 )
IS
BEGIN
   NULL;
END Do_Complete___;

PROCEDURE Do_Reserve___ (
   rec_  IN OUT f8lib_reservations_tab%ROWTYPE,
   attr_ IN OUT VARCHAR2 )
IS
   user_res_count NUMBER;
BEGIN
   SELECT COUNT(*)
     INTO user_res_count
     FROM f8lib_reservations_tab
    WHERE book_name = rec_.book_name
      AND author_id = rec_.author_id
      AND user_id = rec_.user_id
      AND rowstate = 'Reserved';

   IF user_res_count > 0 THEN
      ERROR_SYS.Record_General('lu_name_','ALL_RESERVED: Bu kitap zaten sizin icin rezerve edilmis.', NULL, NULL);
  
    END IF;
   
END Do_Reserve___;

-------------------- LU SPECIFIC PRIVATE METHODS ----------------------------




-------------------- LU SPECIFIC PROTECTED METHODS --------------------------


-------------------- LU SPECIFIC PUBLIC METHODS -----------------------------

PROCEDURE Add_Reservations(book_id_ NUMBER,
                           user_id_ NUMBER) 
IS
 info_       VARCHAR2(32000);
 objid_      VARCHAR2(32000);
 objversion_ VARCHAR2(32000);
 attr_       VARCHAR2(32000);
BEGIN
   Client_SYS.Clear_Attr(attr_);
   Client_SYS.Add_To_Attr('USER_ID',user_id_,attr_);
   Client_SYS.Add_To_Attr('BOOK_NAME',F8lib_Books_API.Get_Book_Name(book_id_),attr_);
   Client_SYS.Add_To_Attr('AUTHOR_ID',F8lib_Books_API.Get_Author_Id(book_id_),attr_);
   Client_SYS.Add_To_Attr('RESERVATION_ID',0,attr_);
   F8lib_Reservations_API.New__(info_ ,
                             objid_,
                             objversion_,
                             attr_,
                             'DO');
 
END Add_Reservations;



FUNCTION Conrol_Reservations(book_id_ NUMBER,
                           user_id_ NUMBER) RETURN NUMBER
IS
 CURSOR control IS 
 SELECT 1
 FROM f8lib_reservations_tab
 WHERE user_id = user_id_
 AND book_name = F8lib_Books_API.Get_Name(book_id_)
 AND rowstate='Reserved';
 control_ NUMBER;
BEGIN
   OPEN control;
   FETCH control INTO control_;
   CLOSE control;
   control_:=NVL(control_,0); 
   RETURN control_;
END Conrol_Reservations;
