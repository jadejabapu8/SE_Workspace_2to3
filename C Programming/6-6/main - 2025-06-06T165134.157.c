#include <stdio.h>

int main()
{
    int a[10];
    int *p;
    int i;
    
    p=&a[0];
    
    printf("\tEnter Values of array\n");
    
    for(int i=0;i<5;i++)
    {
        
          printf("\na[%d]:\t",i+1);
          scanf("%d",&(*p++));
    }
    
     p=&a[0];
    printf("\nValue of Array:\n");
    
    for(i=0;i<5;i++)
    {
          printf("\na[%d]:\t%d\n",i+1,*p++);
    }
    

    return 0;
}