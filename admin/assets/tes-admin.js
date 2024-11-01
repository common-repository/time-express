jQuery(document).on('click','.process-tes-order',function(){
    let order_id=jQuery(this).data('id')
    let action=tes.process_order_action
    let ajaxUrl=tes.ajaxUrl
    let data=new FormData()
    data.append('action',action)
    data.append('order_id',order_id)
    let elem=jQuery(this)
    elem.html("Processing Order...")
    jQuery.ajax({
        url : ajaxUrl,
        type: 'POST',
        contentType: false,
        processData: false,
        data: data,
        success:function(data){
            if(data.tracking_no){
                let track_url='https://www.timexpress.ae/track/?awbno='+data.tracking_no
                elem.parent().html("<a target='_blank' href='"+track_url+"'>T.N."+data.tracking_no+"(Track Status)</a>")
                
                
            }
            else{
                alert("some error Occured! Try After Some time")
                elem.html("Try Again!")
            }
            console.log(data)
        },
        error: function(err){
            alert("some error Occured! Try After Some time")
            elem.html("Try Again!")
            console.log(err)
        }
    })
})